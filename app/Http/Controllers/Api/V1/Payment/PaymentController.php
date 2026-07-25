<?php

namespace App\Http\Controllers\Api\V1\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\InitiatePaymentRequest;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Initiate payment for a booking
     */
    public function store(InitiatePaymentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        $customer = $user->customer;

        if (!$customer) {
            return response()->json([
                'message' => 'Profil customer tidak ditemukan.',
            ], 404);
        }

        $booking = Booking::where('customer_id', $customer->id)
            ->where('id', $validated['booking_id'])
            ->with(['branch', 'service'])
            ->firstOrFail();

        // Check if booking can be paid
        if ($booking->payment_status === \App\Enums\PaymentStatus::PAID) {
            return response()->json([
                'message' => 'Booking sudah dibayar.',
            ], 400);
        }

        if (!in_array($booking->status, [
            \App\Enums\BookingStatus::PENDING_PAYMENT->value,
            \App\Enums\BookingStatus::CONFIRMED->value,
        ])) {
            return response()->json([
                'message' => 'Booking tidak dapat dibayar pada status ini.',
            ], 400);
        }

        // Update booking payment method if different
        if ($booking->payment_method !== $validated['payment_method']) {
            $booking->update(['payment_method' => $validated['payment_method']]);
        }

        // Handle cash payment
        if ($validated['payment_method'] === \App\Enums\PaymentMethod::CASH->value) {
            $booking->update([
                'payment_status' => \App\Enums\PaymentStatus::PAID,
                'status' => \App\Enums\BookingStatus::CONFIRMED,
            ]);

            // Create payment record
            Payment::create([
                'booking_id' => $booking->id,
                'gateway' => 'manual',
                'method' => \App\Enums\PaymentMethod::CASH->value,
                'amount' => $booking->total,
                'status' => 'success',
                'paid_at' => now(),
            ]);

            event(new \App\Events\BookingConfirmed($booking));

            return response()->json([
                'message' => 'Pembayaran tunai dicatat. Booking dikonfirmasi.',
                'data' => [
                    'payment_method' => 'cash',
                    'status' => 'paid',
                ],
            ]);
        }

        // Create Midtrans transaction for digital payments
        try {
            $result = $this->paymentService->createTransaction($booking);

            return response()->json([
                'message' => 'Transaksi pembayaran berhasil dibuat.',
                'data' => [
                    'snap_token' => $result['snap_token'],
                    'redirect_url' => $result['redirect_url'] ?? null,
                    'payment_id' => $booking->payment->id,
                    'expired_at' => $booking->payment->expired_at?->format('Y-m-d H:i:s'),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Payment initiation failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Gagal membuat transaksi pembayaran. Silakan coba lagi.',
            ], 500);
        }
    }

    /**
     * Check payment status
     */
    public function status(string $id): JsonResponse
    {
        $user = Auth::user();
        $customer = $user->customer;

        $payment = Payment::whereHas('booking', function ($query) use ($customer) {
            $query->where('customer_id', $customer->id);
        })->findOrFail($id);

        // If payment is still pending, check with Midtrans
        if (in_array($payment->status, ['pending', 'failed'])) {
            $midtransStatus = $this->paymentService->checkPaymentStatus($payment->gateway_order_id);
            
            if (isset($midtransStatus['transaction_status'])) {
                // The webhook should handle this, but we can also check manually
                // For now, just return current status
            }
        }

        return response()->json([
            'data' => [
                'id' => $payment->id,
                'booking_id' => $payment->booking_id,
                'booking_code' => $payment->booking->booking_code,
                'gateway' => $payment->gateway,
                'method' => $payment->method,
                'amount' => $payment->amount,
                'status' => $payment->status,
                'paid_at' => $payment->paid_at?->format('Y-m-d H:i:s'),
                'expired_at' => $payment->expired_at?->format('Y-m-d H:i:s'),
                'booking_status' => $payment->booking->status?->value,
                'booking_payment_status' => $payment->booking->payment_status?->value,
            ],
        ]);
    }

    /**
     * Midtrans webhook callback
     */
    public function callback(Request $request): JsonResponse
    {
        $payload = $request->all();
        $signatureKey = $request->header('X-Signature') ?? $request->input('signature_key');

        try {
            $this->paymentService->handleWebhook($payload, $signatureKey);
            
            return response()->json([
                'message' => 'Webhook processed successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans webhook error', [
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Webhook processing failed',
            ], 500);
        }
    }

    /**
     * Retry expired/failed payment
     */
    public function retry(string $id): JsonResponse
    {
        $user = Auth::user();
        $customer = $user->customer;

        $payment = Payment::whereHas('booking', function ($query) use ($customer) {
            $query->where('customer_id', $customer->id);
        })->findOrFail($id);

        // Check if payment can be retried
        if (!in_array($payment->status, ['pending', 'failed', 'expired', 'cancelled'])) {
            return response()->json([
                'message' => 'Pembayaran tidak bisa di-retry pada status ini.',
            ], 400);
        }

        try {
            $result = $this->paymentService->retryPayment($payment);

            return response()->json([
                'message' => 'Pembayaran berhasil di-retry.',
                'data' => [
                    'snap_token' => $result['snap_token'],
                    'redirect_url' => $result['redirect_url'] ?? null,
                    'payment_id' => $payment->fresh()->id,
                    'expired_at' => $payment->fresh()->expired_at?->format('Y-m-d H:i:s'),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Payment retry failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Gagal retry pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available payment methods
     */
    public function methods(): JsonResponse
    {
        $methods = $this->paymentService->getAvailablePaymentMethods();

        return response()->json([
            'data' => $methods,
        ]);
    }
}