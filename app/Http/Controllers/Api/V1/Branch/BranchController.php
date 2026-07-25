<?php

namespace App\Http\Controllers\Api\V1\Branch;

use App\Http\Controllers\Controller;
use App\Http\Requests\Branch\SlotRequest;
use App\Models\Branch;
use App\Models\Service;
use App\Models\ServicePricing;
use App\Models\SlotConfiguration;
use App\Services\SlotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    protected SlotService $slotService;

    public function __construct(SlotService $slotService)
    {
        $this->slotService = $slotService;
    }

    /**
     * Get list of branches with optional location filtering
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'numeric', 'min:0.1', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = Branch::query()->with(['servicePricings.service', 'slotConfigurations']);

        // Filter by active status
        if (isset($validated['is_active'])) {
            $query->where('is_active', $validated['is_active']);
        } else {
            $query->where('is_active', true);
        }

        // Search by name, address, city
        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // Location-based filtering (if lat/lng provided)
        if (!empty($validated['lat']) && !empty($validated['lng'])) {
            $radius = $validated['radius'] ?? 10; // default 10km
            $lat = $validated['lat'];
            $lng = $validated['lng'];

            $query->whereRaw(
                "(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) <= ?",
                [$lat, $lng, $lat, $radius]
            )->addSelect([
                'distance' => "(6371 * acos(cos(radians({$lat})) * cos(radians(latitude)) * cos(radians(longitude) - radians({$lng})) + sin(radians({$lat})) * sin(radians(latitude))))"
            ])->orderBy('distance');
        }

        $perPage = $validated['per_page'] ?? 20;
        $branches = $query->paginate($perPage);

        // Add slot availability info for each branch
        $branches->getCollection()->transform(function ($branch) {
            return $this->formatBranch($branch);
        });

        return response()->json([
            'data' => $branches->items(),
            'meta' => [
                'current_page' => $branches->currentPage(),
                'last_page' => $branches->lastPage(),
                'per_page' => $branches->perPage(),
                'total' => $branches->total(),
            ],
        ]);
    }

    /**
     * Get branch detail with operational hours
     */
    public function show(string $id): JsonResponse
    {
        $branch = Branch::with([
            'servicePricings.service',
            'slotConfigurations' => function ($query) {
                $query->where('date', '>=', now()->format('Y-m-d'))
                      ->orWhereNotNull('day_of_week')
                      ->orderBy('date')
                      ->orderBy('slot_time');
            }
        ])->findOrFail($id);

        return response()->json([
            'data' => $this->formatBranchDetail($branch),
        ]);
    }

    /**
     * Get services available at a branch with pricing
     */
    public function services(string $id): JsonResponse
    {
        $branch = Branch::findOrFail($id);

        $services = Service::where('is_active', true)
            ->whereHas('servicePricings', function ($query) use ($branch) {
                $query->where('branch_id', $branch->id)
                      ->where('is_active', true);
            })
            ->with(['servicePricings' => function ($query) use ($branch) {
                $query->where('branch_id', $branch->id)
                      ->where('is_active', true)
                      ->with('service');
            }, 'addOns' => function ($query) {
                $query->where('is_active', true);
            }])
            ->orderBy('sort_order')
            ->get();

        $formattedServices = $services->map(function ($service) use ($branch) {
            $pricings = $service->servicePricings->keyBy('vehicle_type');
            
            return [
                'id' => $service->id,
                'name' => $service->name,
                'slug' => $service->slug,
                'description' => $service->description,
                'category' => $service->category->value,
                'base_duration_minutes' => $service->base_duration_minutes,
                'image' => $service->image,
                'pricings' => $pricings->map(function ($pricing) {
                    return [
                        'vehicle_type' => $pricing->vehicle_type->value,
                        'price' => $pricing->price,
                        'duration_minutes' => $pricing->duration_minutes,
                    ];
                }),
                'add_ons' => $service->addOns->map(function ($addon) {
                    return [
                        'id' => $addon->id,
                        'name' => $addon->name,
                        'description' => $addon->description,
                        'price' => $addon->price,
                        'duration_minutes' => $addon->duration_minutes,
                    ];
                }),
            ];
        });

        return response()->json([
            'data' => $formattedServices,
        ]);
    }

    /**
     * Get available slots for a branch on a specific date
     */
    public function slots(string $id, SlotRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $date = $validated['date'];

        $branch = Branch::findOrFail($id);

        // Check if branch is open on this day
        if (!$this->isBranchOpenOnDate($branch, $date)) {
            return response()->json([
                'message' => 'Cabang tutup pada tanggal ini.',
                'data' => [],
            ], 400);
        }

        $slots = $this->slotService->getAvailableSlots($branch, $date);

        return response()->json([
            'data' => $slots,
            'date' => $date,
            'branch_id' => $branch->id,
            'branch_name' => $branch->name,
        ]);
    }

    /**
     * Format branch for list view
     */
    private function formatBranch(Branch $branch): array
    {
        $today = now()->format('Y-m-d');
        $slotAvailability = $this->slotService->getAvailableSlots($branch, $today);
        
        $totalSlots = collect($slotAvailability)->sum('max_capacity');
        $bookedSlots = collect($slotAvailability)->sum('booked_count');
        $availableSlots = $totalSlots - $bookedSlots;

        return [
            'id' => $branch->id,
            'name' => $branch->name,
            'slug' => $branch->slug,
            'address' => $branch->address,
            'city' => $branch->city,
            'province' => $branch->province,
            'postal_code' => $branch->postal_code,
            'phone' => $branch->phone,
            'email' => $branch->email,
            'latitude' => $branch->latitude ? (float) $branch->latitude : null,
            'longitude' => $branch->longitude ? (float) $branch->longitude : null,
            'distance' => isset($branch->distance) ? round((float) $branch->distance, 2) : null,
            'is_active' => $branch->is_active,
            'cover_image' => $branch->cover_image,
            'operational_notes' => $branch->operational_notes,
            'today_slots' => [
                'total' => $totalSlots,
                'available' => max(0, $availableSlots),
                'booked' => $bookedSlots,
            ],
            'opening_hours' => $branch->opening_hours,
        ];
    }

    /**
     * Format branch for detail view
     */
    private function formatBranchDetail(Branch $branch): array
    {
        return [
            'id' => $branch->id,
            'name' => $branch->name,
            'slug' => $branch->slug,
            'address' => $branch->address,
            'city' => $branch->city,
            'province' => $branch->province,
            'postal_code' => $branch->postal_code,
            'phone' => $branch->phone,
            'email' => $branch->email,
            'latitude' => $branch->latitude ? (float) $branch->latitude : null,
            'longitude' => $branch->longitude ? (float) $branch->longitude : null,
            'is_active' => $branch->is_active,
            'cover_image' => $branch->cover_image,
            'operational_notes' => $branch->operational_notes,
            'opening_hours' => $branch->opening_hours,
            'daily_max_capacity' => $branch->daily_max_capacity,
            'slot_configurations' => $branch->slotConfigurations->map(function ($slot) {
                return [
                    'id' => $slot->id,
                    'date' => $slot->date?->format('Y-m-d'),
                    'day_of_week' => $slot->day_of_week,
                    'slot_time' => $slot->slot_time?->format('H:i'),
                    'max_capacity' => $slot->max_capacity,
                    'current_booked' => $slot->current_booked,
                    'is_blocked' => $slot->is_blocked,
                    'block_reason' => $slot->block_reason,
                    'notes' => $slot->notes,
                    'type' => $slot->type,
                ];
            }),
        ];
    }

    /**
     * Check if branch is open on a specific date
     */
    private function isBranchOpenOnDate(Branch $branch, string $date): bool
    {
        $carbonDate = \Carbon\Carbon::parse($date);
        $dayOfWeek = strtolower($carbonDate->format('l')); // monday, tuesday, etc.
        
        $openingHours = $branch->opening_hours ?? [];
        $dayHours = $openingHours[$dayOfWeek] ?? null;

        if (!$dayHours || empty($dayHours['open']) || empty($dayHours['close'])) {
            return false;
        }

        // Check if specific date is blocked
        $isBlocked = SlotConfiguration::where('branch_id', $branch->id)
            ->where('date', $date)
            ->where('is_blocked', true)
            ->exists();

        if ($isBlocked) {
            return false;
        }

        return true;
    }
}