<?php

namespace App\Http\Controllers\Api\V1\Promo;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use App\Models\PromoUsage;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    /**
     * Get available promos for customer
     */
    public function available(Request $request): JsonResponse
    {
        $user = $request->user();
        $customer = $user->customer;

        if (!$customer) {
            return response()->json([
                'message' => 'Profil customer tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'branch_id' => ['nullable', 'uuid', 'exists:branches,id'],
            'service_id' => ['nullable', 'uuid', 'exists:services,id'],
            'min_order' => ['nullable', 'numeric', 'min:0'],
        ]);

        $query = Promo::where('is_active', true)
            ->where('start_date', '<=', now()->format('Y-m-d'))
            ->where('end_date', '>=', now()->format('Y-m-d'))
            ->where(function ($q) {
                $q->whereNull('usage_limit')
                  ->orWhereRaw('used_count < usage_limit');
            });

        // Filter by branch
        if (!empty($validated['branch_id'])) {
            $query->where(function ($q) use ($validated) {
                $q->whereNull('applicable_branches')
                  ->orWhereJsonContains('applicable_branches', $validated['branch_id']);
            });
        }

        // Filter by service
        if (!empty($validated['service_id'])) {
            $query->where(function ($q) use ($validated) {
                $q->whereNull('applicable_services')
                  ->orWhereJsonContains('applicable_services', $validated['service_id']);
            });
        }

        // Filter by minimum order
        if (!empty($validated['min_order'])) {
            $query->where('min_order', '<=', $validated['min_order']);
        }

        $promos = $query->orderBy('discount_value', 'desc')
            ->get()
            ->filter(function ($promo) use ($customer) {
                return $promo->canBeUsedBy($customer);
            })
            ->map(function ($promo) {
                return [
                    'id' => $promo->id,
                    'code' => $promo->code,
                    'name' => $promo->name,
                    'description' => $promo->description,
                    'discount_type' => $promo->discount_type,
                    'discount_value' => $promo->discount_value,
                    'min_order' => $promo->min_order,
                    'max_discount' => $promo->max_discount,
                    'start_date' => $promo->start_date?->format('Y-m-d'),
                    'end_date' => $promo->end_date?->format('Y-m-d'),
                    'usage_limit' => $promo->usage_limit,
                    'used_count' => $promo->used_count,
                    'remaining_usage' => $promo->usage_limit ? $promo->usage_limit - $promo->used_count : null,
                ];
            })
            ->values();

        return response()->json([
            'data' => $promos,
        ]);
    }

    /**
     * Apply promo code and calculate discount
     */
    public function apply(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'branch_id' => ['nullable', 'uuid', 'exists:branches,id'],
            'service_id' => ['nullable', 'uuid', 'exists:services,id'],
        ]);

        $user = $request->user();
        $customer = $user->customer;

        if (!$customer) {
            return response()->json([
                'message' => 'Profil customer tidak ditemukan.',
            ], 404);
        }

        $promo = Promo::where('code', $validated['code'])
            ->where('is_active', true)
            ->where('start_date', '<=', now()->format('Y-m-d'))
            ->where('end_date', '>=', now()->format('Y-m-d'))
            ->first();

        if (!$promo) {
            return response()->json([
                'valid' => false,
                'message' => 'Kode promo tidak valid atau sudah kadaluarsa.',
            ], 400);
        }

        // Check usage limit
        if ($promo->usage_limit && $promo->used_count >= $promo->usage_limit) {
            return response()->json([
                'valid' => false,
                'message' => 'Kode promo sudah mencapai batas penggunaan.',
            ], 400);
        }

        // Check if customer already used this promo
        $hasUsed = PromoUsage::where('promo_id', $promo->id)
            ->where('customer_id', $customer->id)
            ->exists();

        if ($hasUsed && $promo->usage_limit_per_customer ?? 1 <= 1) {
            return response()->json([
                'valid' => false,
                'message' => 'Anda sudah menggunakan kode promo ini.',
            ], 400);
        }

        // Check minimum order
        if ($promo->min_order > $validated['subtotal']) {
            return response()->json([
                'valid' => false,
                'message' => 'Minimum order untuk promo ini adalah Rp ' . number_format($promo->min_order, 0, ',', '.'),
            ], 400);
        }

        // Check branch applicability
        if (!empty($validated['branch_id']) && $promo->applicable_branches) {
            if (!in_array($validated['branch_id'], $promo->applicable_branches)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Promo tidak berlaku untuk cabang ini.',
                ], 400);
            }
        }

        // Check service applicability
        if (!empty($validated['service_id']) && $promo->applicable_services) {
            if (!in_array($validated['service_id'], $promo->applicable_services)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Promo tidak berlaku untuk layanan ini.',
                ], 400);
            }
        }

        // Calculate discount
        $discount = $promo->calculateDiscount($validated['subtotal']);

        return response()->json([
            'valid' => true,
            'message' => 'Kode promo berhasil diterapkan.',
            'data' => [
                'promo_id' => $promo->id,
                'code' => $promo->code,
                'name' => $promo->name,
                'discount_type' => $promo->discount_type,
                'discount_value' => $promo->discount_value,
                'discount_amount' => $discount,
                'min_order' => $promo->min_order,
                'max_discount' => $promo->max_discount,
            ],
        ]);
    }
}