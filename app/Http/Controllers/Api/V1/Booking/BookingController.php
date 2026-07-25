<?php

namespace App\Http\Controllers\Api\V1\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\CreateBookingRequest;
use App\Http\Requests\Booking\CancelBookingRequest;
use App\Http\Requests\Booking\ReviewBookingRequest;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\ServicePricing;
use App\Models\AddOn;
use App\Models\Promo;
use App\Models\PromoUsage;
use App\Services\SlotService;
use App\Actions\Booking\CreateBookingAction;
use App\Actions\Booking\CancelBookingAction;
use App\Actions\Booking\ReviewBookingAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    protected SlotService $slotService;

    public function __construct(SlotService $slotService)
    {
        $this->slotService = $slotService;
    }

    /**
     * Create a new booking
     */
    public function store(CreateBookingRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        $customer = $user->customer;

        if (!$customer) {
            return response()->json([
                'message' => 'Profil customer tidak ditemukan.',
            ], 404);
        }

        // Check if branch is open on this date
        $branch = $customer->branch; // or get from validated
        $branch = \App\Models\Branch::findOrFail($validated['branch_id']);
        
        if (!$this->isBranchOpenOnDate($branch, $validated['booking_date'])) {
            return response()->json([
                'message' => 'Cabang tutup pada tanggal ini.',
            ], 400);
        }

        // Check cut-off time and slot availability
        $canBook = $this->slotService->canBookSlot($branch, $validated['booking_date'], $validated['slot_time']);
        if (!$canBook['can_book']) {
            return response()->json([
                'message' => $canBook['reason'],
            ], 400);
        }

        // Check if slot is still available (double-check with locking)
        if (!$this->slotService->isSlotAvailable($branch, $validated['booking_date'], $validated['slot_time'])) {
            return response()->json([
                'message' => 'Slot sudah penuh atau tidak tersedia.',
            ], 409);
        }

        // Use database transaction with locking
        $booking = DB::transaction(function () use ($validated, $customer, $branch) {
            // Lock and increment slot count
            $this->slotService->incrementBookedCount($branch, $validated['booking_date'], $validated['slot_time']);

            // Calculate pricing
            $pricing = ServicePricing::where('service_id', $validated['service_id'])
                ->where('branch_id', $validated['branch_id'])
                ->where('vehicle_type', $validated['vehicle_type'])
                ->where('is_active', true)
                ->firstOrFail();

            $subtotal = $pricing->price;
            $discount = 0;
            $promoId = null;

            // Apply promo if provided
            if (!empty($validated['promo_code'])) {
                $promo = Promo::where('code', $validated['promo_code'])
                    ->where('is_active', true)
                    ->where('start_date', '<=', $validated['booking_date'])
                    ->where('end_date', '>=', $validated['booking_date'])
                    ->first();

                if ($promo && $promo->canBeUsedBy($customer)) {
                    $discount = $promo->calculateDiscount($subtotal);
                    $promoId = $promo->id;
                }
            }

            // Add-on pricing
            $addOnPrice = 0;
            if (!empty($validated['add_on_ids'])) {
                $addOns = AddOn::whereIn('id', $validated['add_on_ids'])
                    ->where('is_active', true)
                    ->get();
                $addOnPrice = $addOns->sum('price');
            }

            // Calculate tax (11% PPN)
            $taxRate = config('settings.tax_rate', 11);
            $taxableAmount = $subtotal + $addOnPrice - $discount;
            $tax = round($taxableAmount * ($taxRate / 100), 2);
            $total = $taxableAmount + $tax;

            // Generate booking code: CK-YYYYMMDD-NNN
            $dateStr = Carbon::parse($validated['booking_date'])->format('Ymd');
            $lastBooking = Booking::where('booking_code', 'like', "CK-{$dateStr}-%")
                ->orderBy('booking_code', 'desc')
                ->first();
            
            $sequence = 1;
            if ($lastBooking) {
                $parts = explode('-', $lastBooking->booking_code);
                $sequence = (int) end($parts) + 1;
            }
            $bookingCode = sprintf('CK-%s-%03d', $dateStr, $sequence);

            // Create booking
            $booking = Booking::create([
                'booking_code' => $bookingCode,
                'customer_id' => $customer->id,
                'branch_id' => $validated['branch_id'],
                'service_id' => $validated['service_id'],
                'vehicle_type' => $validated['vehicle_type'],
                'vehicle_color' => $validated['vehicle_color'] ?? null,
                'vehicle_plate' => $validated['vehicle_plate'] ?? null,
                'vehicle_photos' => $validated['vehicle_photos'] ?? [],
                'booking_date' => $validated['booking_date'],
                'slot_time' => $validated['slot_time'],
                'status' => \App\Enums\BookingStatus::PENDING_PAYMENT,
                'payment_method' => $validated['payment_method'],
                'payment_status' => \App\Enums\PaymentStatus::UNPAID,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
                'is_walk_in' => $validated['is_walk_in'] ?? false,
            ]);

            // Attach add-ons
            if (!empty($validated['add_on_ids'])) {
                foreach ($validated['add_on_ids'] as $addOnId) {
                    $addOn = AddOn::find($addOnId);
                    if ($addOn) {
                        $booking->bookingAddOns()->create([
                            'add_on_id' => $addOn->id,
                            'price' => $addOn->price,
                        ]);
                    }
                }
            }

            // Record promo usage
            if ($promoId) {
                PromoUsage::create([
                    'promo_id' => $promoId,
                    'booking_id' => $booking->id,
                    'customer_id' => $customer->id,
                    'discount_amount' => $discount,
                ]);
            }

            // Log status
            $booking->bookingStatusLogs()->create([
                'from_status' => null,
                'to_status' => \App\Enums\BookingStatus::PENDING_PAYMENT->value,
                'changed_by' => $customer->user_id,
                'notes' => 'Booking created',
            ]);

            return $booking->load(['service', 'branch', 'bookingAddOns.addOn', 'customer']);
        });

        // If payment method is cash, confirm immediately
        if ($validated['payment_method'] === \App\Enums\PaymentMethod::CASH->value) {
            $booking->update([
                'status' => \App\Enums\BookingStatus::CONFIRMED,
                'payment_status' => \App\Enums\PaymentStatus::PAID,
            ]);
            
            event(new \App\Events\BookingConfirmed($booking));
        }

        return response()->json([
            'message' => 'Booking berhasil dibuat.',
            'data' => $this->formatBooking($booking),
        ], 201);
    }

    /**
     * Get customer's booking history
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $user = $request->user();
        $customer = $user->customer;

        if (!$customer) {
            return response()->json([
                'message' => 'Profil customer tidak ditemukan.',
            ], 404);
        }

        $query = Booking::where('customer_id', $customer->id)
            ->with(['service', 'branch', 'bookingAddOns.addOn', 'payment'])
            ->orderBy('created_at', 'desc');

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['start_date'])) {
            $query->where('booking_date', '>=', $validated['start_date']);
        }

        if (!empty($validated['end_date'])) {
            $query->where('booking_date', '<=', $validated['end_date']);
        }

        $perPage = $validated['per_page'] ?? 15;
        $bookings = $query->paginate($perPage);

        return response()->json([
            'data' => $bookings->getCollection()->map(fn($b) => $this->formatBooking($b)),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
            ],
        ]);
    }

    /**
     * Get booking detail
     */
    public function show(string $id): JsonResponse
    {
        $user = Auth::user();
        $customer = $user->customer;

        $booking = Booking::where('customer_id', $customer->id)
            ->with(['service', 'branch', 'bookingAddOns.addOn', 'payment', 'bookingStatusLogs'])
            ->findOrFail($id);

        return response()->json([
            'data' => $this->formatBookingDetail($booking),
        ]);
    }

    /**
     * Cancel booking
     */
    public function cancel(string $id, CancelBookingRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        $customer = $user->customer;

        $booking = Booking::where('customer_id', $customer->id)
            ->where('id', $id)
            ->firstOrFail();

        // Check if booking can be cancelled
        $cancellableStatuses = [
            \App\Enums\BookingStatus::PENDING_PAYMENT->value,
            \App\Enums\BookingStatus::CONFIRMED->value,
        ];

        if (!in_array($booking->status, $cancellableStatuses)) {
            return response()->json([
                'message' => 'Booking tidak dapat dibatalkan pada status ini.',
            ], 400);
        }

        DB::transaction(function () use ($booking, $validated, $user) {
            // Decrement slot count
            $this->slotService->decrementBookedCount(
                $booking->branch,
                $booking->booking_date,
                $booking->slot_time
            );

            // Update booking
            $booking->cancel($validated['cancel_reason'], $user->id);

            // Log status
            $booking->bookingStatusLogs()->create([
                'from_status' => $booking->getOriginal('status'),
                'to_status' => \App\Enums\BookingStatus::CANCELLED->value,
                'changed_by' => $user->id,
                'notes' => $validated['cancel_reason'],
            ]);

            // Handle refund if already paid
            if ($booking->payment_status === \App\Enums\PaymentStatus::PAID) {
                $booking->update(['payment_status' => \App\Enums\PaymentStatus::REFUND_PENDING]);
                // TODO: Trigger refund process
            }
        });

        return response()->json([
            'message' => 'Booking berhasil dibatalkan.',
        ]);
    }

    /**
     * Public tracking by booking code
     */
    public function track(string $code): JsonResponse
    {
        $booking = Booking::where('booking_code', $code)
            ->with(['service', 'branch', 'bookingAddOns.addOn', 'bookingStatusLogs', 'customer.user'])
            ->firstOrFail();

        return response()->json([
            'data' => $this->formatBookingDetail($booking, true),
        ]);
    }

    /**
     * Submit review for completed booking
     */
    public function review(string $id, ReviewBookingRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        $customer = $user->customer;

        $booking = Booking::where('customer_id', $customer->id)
            ->where('id', $id)
            ->where('status', \App\Enums\BookingStatus::COMPLETED)
            ->firstOrFail();

        if ($booking->rating !== null) {
            return response()->json([
                'message' => 'Booking sudah diberi rating.',
            ], 400);
        }

        $booking->update([
            'rating' => $validated['rating'],
            'review' => $validated['review'] ?? null,
            'review_photos' => $validated['review_photos'] ?? [],
            'reviewed_at' => now(),
        ]);

        // Update customer satisfaction
        $customer->updateLoyaltyTier();

        return response()->json([
            'message' => 'Terima kasih atas ulasan Anda!',
            'data' => $this->formatBooking($booking->fresh()),
        ]);
    }

    /**
     * Format booking for list view
     */
    private function formatBooking(Booking $booking): array
    {
        return [
            'id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'service' => [
                'id' => $booking->service->id,
                'name' => $booking->service->name,
                'category' => $booking->service->category->value,
            ],
            'branch' => [
                'id' => $booking->branch->id,
                'name' => $booking->branch->name,
                'address' => $booking->branch->address,
            ],
            'vehicle_type' => $booking->vehicle_type?->value,
            'vehicle_color' => $booking->vehicle_color,
            'vehicle_plate' => $booking->vehicle_plate,
            'booking_date' => $booking->booking_date?->format('Y-m-d'),
            'slot_time' => $booking->slot_time?->format('H:i'),
            'status' => $booking->status?->value,
            'status_label' => $booking->status ? $this->getStatusLabel($booking->status->value) : null,
            'payment_method' => $booking->payment_method?->value,
            'payment_status' => $booking->payment_status?->value,
            'subtotal' => $booking->subtotal,
            'discount' => $booking->discount,
            'tax' => $booking->tax,
            'total' => $booking->total,
            'rating' => $booking->rating,
            'review' => $booking->review,
            'created_at' => $booking->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Format booking for detail view
     */
    private function formatBookingDetail(Booking $booking, bool $isPublic = false): array
    {
        $data = $this->formatBooking($booking);
        
        $data['add_ons'] = $booking->bookingAddOns->map(function ($addon) {
            return [
                'id' => $addon->id,
                'name' => $addon->addOn->name,
                'price' => $addon->price,
            ];
        });

        $data['status_logs'] = $booking->bookingStatusLogs->map(function ($log) {
            return [
                'from_status' => $log->from_status,
                'to_status' => $log->to_status,
                'to_status_label' => $this->getStatusLabel($log->to_status),
                'changed_by' => $log->changed_by,
                'notes' => $log->notes,
                'created_at' => $log->created_at?->format('Y-m-d H:i:s'),
            ];
        });

        if (!$isPublic) {
            $data['payment'] = $booking->payment ? [
                'id' => $booking->payment->id,
                'gateway' => $booking->payment->gateway,
                'method' => $booking->payment->method,
                'amount' => $booking->payment->amount,
                'status' => $booking->payment->status,
                'paid_at' => $booking->payment->paid_at?->format('Y-m-d H:i:s'),
                'expired_at' => $booking->payment->expired_at?->format('Y-m-d H:i:s'),
            ] : null;
        }

        return $data;
    }

    /**
     * Get human-readable status label
     */
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'pending_payment' => 'Menunggu Pembayaran',
            'confirmed' => 'Dikonfirmasi',
            'in_queue' => 'Dalam Antrian',
            'washing' => 'Sedang Dicuci',
            'drying' => 'Pengeringan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            'no_show' => 'Tidak Hadir',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    /**
     * Check if branch is open on a specific date
     */
    private function isBranchOpenOnDate(\App\Models\Branch $branch, string $date): bool
    {
        $carbonDate = Carbon::parse($date);
        $dayOfWeek = strtolower($carbonDate->format('l'));
        
        $openingHours = $branch->opening_hours ?? [];
        $dayHours = $openingHours[$dayOfWeek] ?? null;

        if (!$dayHours || empty($dayHours['open']) || empty($dayHours['close'])) {
            return false;
        }

        // Check if specific date is blocked
        $isBlocked = \App\Models\SlotConfiguration::where('branch_id', $branch->id)
            ->where('date', $date)
            ->where('is_blocked', true)
            ->exists();

        if ($isBlocked) {
            return false;
        }

        return true;
    }
}