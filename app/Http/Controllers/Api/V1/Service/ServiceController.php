<?php

namespace App\Http\Controllers\Api\V1\Service;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServicePricing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Get all active services
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'uuid', 'exists:branches,id'],
            'category' => ['nullable', 'string', 'in:basic,premium,premium_plus,on_demand'],
            'vehicle_type' => ['nullable', 'string', 'in:sedan,suv,mpv,hatchback,motor,truck'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = Service::where('is_active', true)
            ->with(['servicePricings' => function ($q) use ($validated) {
                $q->where('is_active', true);
                
                if (!empty($validated['branch_id'])) {
                    $q->where('branch_id', $validated['branch_id']);
                }
                
                if (!empty($validated['vehicle_type'])) {
                    $q->where('vehicle_type', $validated['vehicle_type']);
                }
            }, 'addOns' => function ($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('sort_order')
            ->orderBy('name');

        if (!empty($validated['category'])) {
            $query->where('category', $validated['category']);
        }

        $perPage = $validated['per_page'] ?? 20;
        $services = $query->paginate($perPage);

        $formattedServices = $services->getCollection()->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'slug' => $service->slug,
                'description' => $service->description,
                'category' => $service->category->value,
                'base_duration_minutes' => $service->base_duration_minutes,
                'image' => $service->image,
                'pricings' => $service->servicePricings->map(function ($pricing) {
                    return [
                        'branch_id' => $pricing->branch_id,
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
            'meta' => [
                'current_page' => $services->currentPage(),
                'last_page' => $services->lastPage(),
                'per_page' => $services->perPage(),
                'total' => $services->total(),
            ],
        ]);
    }

    /**
     * Get service detail with pricing
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'uuid', 'exists:branches,id'],
            'vehicle_type' => ['nullable', 'string', 'in:sedan,suv,mpv,hatchback,motor,truck'],
        ]);

        $service = Service::where('is_active', true)
            ->with(['servicePricings' => function ($q) use ($validated) {
                $q->where('is_active', true);
                
                if (!empty($validated['branch_id'])) {
                    $q->where('branch_id', $validated['branch_id']);
                }
                
                if (!empty($validated['vehicle_type'])) {
                    $q->where('vehicle_type', $validated['vehicle_type']);
                }
            }, 'addOns' => function ($q) {
                $q->where('is_active', true);
            }])
            ->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $service->id,
                'name' => $service->name,
                'slug' => $service->slug,
                'description' => $service->description,
                'category' => $service->category->value,
                'base_duration_minutes' => $service->base_duration_minutes,
                'image' => $service->image,
                'pricings' => $service->servicePricings->map(function ($pricing) {
                    return [
                        'branch_id' => $pricing->branch_id,
                        'branch_name' => $pricing->branch?->name,
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
            ],
        ]);
    }
}