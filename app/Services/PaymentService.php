<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

class PaymentService
{
    protected string $serverKey;
    protected string $clientKey;
    protected bool $isProduction;
    protected string $midtransUrl;

    public function __construct()
    {
        $this->serverKey = config('services.midtrans.server_key', env('MIDTRANS_SERVER_KEY'));
        $this->clientKey = config('services.midtrans.client_key', env('MIDTRANS_CLIENT_KEY'));
        $this->isProduction = config('services.midtrans.is_production', env('MIDTRANS_IS_PRODUCTION', false));
        $this->midtransUrl = $this->isProduction 
            ? 'https://app.midtrans.com' 
            : 'https://app.sandbox.midtrans.com';

        // Configure Midtrans
        MidtransConfig::$serverKey = $this->serverKey;
        MidtransConfig::$isProduction = $this->isProduction;
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;
    }

    /**
     * Create Midtrans Snap transaction
     */
    public function createTransaction(Booking $booking): array
    {
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'gateway' => 'midtrans',
            'method' => $booking->payment_method,
            'amount' => $booking->total,
            'status' => 'pending',
            'expired_at' => now()->addMinutes(config('settings.payment_expired_minutes', 30)),
        ]);

        try {
            $payload = $this->buildPayload($booking);

            // Create transaction via Midtrans Snap
            $snapToken = Snap::getSnapToken($payload);
            $redirectUrl = $this->midtransUrl . '/snap/v3/redirection/' . $snapToken;

            // Update payment with gateway info
            $payment->update([
                'gateway_order_id' => $booking->booking_code,
                'gateway_payment_id' => null, // Will be filled on webhook
            ]);

            return [
                'snap_token' => $snapToken,
                'redirect_url' => $redirectUrl,
                'payment_id' => $payment->id,
            ];

        } catch (\Exception $e) {
            Log::error('Midtrans create transaction failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            $payment->update(['status' => 'failed']);
            
            throw new \Exception('Gagal membuat transaksi pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Build Midtrans payload
     */
    protected function buildPayload(Booking $booking): array
    {
        $customer = $booking->customer;
        $user = $customer->user;

        $itemDetails = [
            [
                'id' => $booking->service_id,
                'price' => (int) $booking->subtotal,
                'quantity' => 1,
                'name' => $booking->service->name . ' - ' . $booking->vehicle_type->getLabel(),
            ],
        ];

        // Add add-ons
        foreach ($booking->bookingAddOns as $addon) {
            $itemDetails[] = [
                'id' => 'addon_' . $addon->add_on_id,
                'price' => (int) $addon->price,
                'quantity' => 1,
                'name' => 'Add-on: ' . $addon->addOn->name,
            ];
        }

        // Add discount as negative item if applicable
        if ($booking->discount > 0) {
            $itemDetails[] = [
                'id' => 'discount',
                'price' => -(int) $booking->discount,
                'quantity' => 1,
                'name' => 'Diskon Promo',
            ];
        }

        // Add tax
        if ($booking->tax > 0) {
            $itemDetails[] = [
                'id' => 'tax',
                'price' => (int) $booking->tax,
                'quantity' => 1,
                'name' => 'PPN ' . config('settings.tax_rate', 11) . '%',
            ];
        }

        return [
            'transaction_details' => [
                'order_id' => $booking->booking_code,
                'gross_amount' => (int) $booking->total,
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
            ],
            'callbacks' => [
                'finish' => config('app.url') . '/payment/finish',
                'error' => config('app.url') . '/payment/error',
                'pending' => config('app.url') . '/payment/pending',
            ],
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s T'),
                'unit' => 'minute',
                'duration' => config('settings.payment_expired_minutes', 30),
            ],
            'custom_field1' => $booking->id, // Store booking ID for webhook reference
        ];
    }

    /**
     * Handle Midtrans webhook notification
     */
    public function handleWebhook(array $payload): void
    {
        $orderId = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;

        if (!$orderId || !$transactionStatus) {
            Log::warning('Invalid Midtrans webhook payload', ['payload' => $payload]);
            return;
        }

        // Verify signature
        if (!$this->verifySignature($payload, $signatureKey)) {
            Log::warning('Invalid Midtrans signature', ['order_id' => $orderId]);
            return;
        }

        $booking = Booking::where('booking_code', $orderId)->first();
        if (!$booking) {
            Log::warning('Booking not found for Midtrans webhook', ['order_id' => $orderId]);
            return;
        }

        $payment = $booking->payment;
        if (!$payment) {
            Log::warning('Payment not found for booking', ['booking_id' => $booking->id]);
            return;
        }

        // Map Midtrans status to our status
        $statusMap = [
            'capture' => 'success',
            'settlement' => 'success',
            'pending' => 'pending',
            'deny' => 'failed',
            'expire' => 'failed',
            'cancel' => 'failed',
            'refund' => 'refunded',
            'partial_refund' => 'refunded',
        ];

        $newStatus = $statusMap[$transactionStatus] ?? 'pending';

        // For capture, check fraud status
        if ($transactionStatus === 'capture') {
            if (in_array($fraudStatus, ['challenge', 'deny'])) {
                $newStatus = 'failed';
            }
        }

        // Update payment record
        $payment->update([
            'status' => $newStatus,
            'raw_response' => $payload,
            'paid_at' => in_array($transactionStatus, ['capture', 'settlement']) ? now() : null,
            'gateway_payment_id' => $payload['transaction_id'] ?? $payment->gateway_payment_id,
        ]);

        // Update booking payment status
        if (in_array($transactionStatus, ['capture', 'settlement'])) {
            $booking->update(['payment_status' => 'paid']);
            
            // If booking was pending payment, confirm it
            if ($booking->status === \App\Enums\BookingStatus::PENDING_PAYMENT) {
                $booking->update(['status' => \App\Enums\BookingStatus::CONFIRMED]);
            }
            
            // Fire event for confirmed booking
            event(new \App\Events\BookingConfirmed($booking));
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $booking->update(['payment_status' => 'unpaid']);
        } elseif (in_array($transactionStatus, ['refund', 'partial_refund'])) {
            $booking->update(['payment_status' => 'refunded']);
        }

        Log::info('Midtrans webhook processed', [
            'order_id' => $orderId,
            'transaction_status' => $transactionStatus,
            'new_payment_status' => $newStatus,
            'booking_id' => $booking->id,
        ]);
    }

    /**
     * Verify Midtrans signature
     */
    protected function verifySignature(array $payload, ?string $signatureKey): bool
    {
        if (!$signatureKey) {
            return false;
        }

        $serverKey = $this->serverKey;
        $hashed = hash('sha512', 
            $payload['order_id'] . 
            $payload['status_code'] . 
            $payload['gross_amount'] . 
            $serverKey
        );

        return hash_equals($hashed, $signatureKey);
    }

    /**
     * Check payment status from Midtrans
     */
    public function checkPaymentStatus(string $orderId): array
    {
        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->get("{$this->midtransUrl}/v2/{$orderId}/status");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Midtrans status check failed', [
                'order_id' => $orderId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['status' => 'error'];

        } catch (\Exception $e) {
            Log::error('Midtrans status check exception', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return ['status' => 'error'];
        }
    }

    /**
     * Retry expired payment
     */
    public function retryPayment(Payment $payment): array
    {
        // Check if payment can be retried
        if (!in_array($payment->status, ['pending', 'failed', 'expired'])) {
            throw new \Exception('Pembayaran tidak bisa di-retry pada status ini.');
        }

        $booking = $payment->booking;
        
        // Create new payment record
        $newPayment = Payment::create([
            'booking_id' => $booking->id,
            'gateway' => 'midtrans',
            'method' => $booking->payment_method,
            'amount' => $booking->total,
            'status' => 'pending',
            'expired_at' => now()->addMinutes(config('settings.payment_expired_minutes', 30)),
        ]);

        // Mark old payment as cancelled
        $payment->update(['status' => 'cancelled']);

        // Create new transaction
        return $this->createTransaction($booking);
    }

    /**
     * Get payment methods for a booking
     */
    public function getAvailablePaymentMethods(): array
    {
        return [
            [
                'code' => 'qris',
                'name' => 'QRIS',
                'description' => 'Bayar dengan scan QR dari e-wallet manapun',
                'icon' => 'qris',
                'min_amount' => 1000,
            ],
            [
                'code' => 'gopay',
                'name' => 'GoPay',
                'description' => 'Bayar dengan GoPay',
                'icon' => 'gopay',
                'min_amount' => 1000,
            ],
            [
                'code' => 'ovo',
                'name' => 'OVO',
                'description' => 'Bayar dengan OVO',
                'icon' => 'ovo',
                'min_amount' => 1000,
            ],
            [
                'code' => 'dana',
                'name' => 'DANA',
                'description' => 'Bayar dengan DANA',
                'icon' => 'dana',
                'min_amount' => 1000,
            ],
            [
                'code' => 'shopeepay',
                'name' => 'ShopeePay',
                'description' => 'Bayar dengan ShopeePay',
                'icon' => 'shopeepay',
                'min_amount' => 1000,
            ],
            [
                'code' => 'va_bca',
                'name' => 'Virtual Account BCA',
                'description' => 'Transfer via VA BCA',
                'icon' => 'bca',
                'min_amount' => 50000,
            ],
            [
                'code' => 'va_bri',
                'name' => 'Virtual Account BRI',
                'description' => 'Transfer via VA BRI',
                'icon' => 'bri',
                'min_amount' => 50000,
            ],
            [
                'code' => 'va_mandiri',
                'name' => 'Virtual Account Mandiri',
                'description' => 'Transfer via VA Mandiri',
                'icon' => 'mandiri',
                'min_amount' => 50000,
            ],
            [
                'code' => 'va_bni',
                'name' => 'Virtual Account BNI',
                'description' => 'Transfer via VA BNI',
                'icon' => 'bni',
                'min_amount' => 50000,
            ],
            [
                'code' => 'credit_card',
                'name' => 'Kartu Kredit/Debit',
                'description' => 'Bayar dengan kartu kredit/debit',
                'icon' => 'credit_card',
                'min_amount' => 10000,
            ],
            [
                'code' => 'cash',
                'name' => 'Tunai di Tempat',
                'description' => 'Bayar tunai saat kedatangan',
                'icon' => 'cash',
                'min_amount' => 0,
            ],
        ];
    }
}