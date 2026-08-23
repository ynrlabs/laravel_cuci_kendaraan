<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;

class QrCodeSecurityService
{
    /**
     * Generate secure, HMAC-signed and tamper-proof payload for a booking QR Code.
     *
     * @param Booking $booking
     * @param int $validityHours Number of hours the QR is valid around booking time (default: 12)
     * @return array
     */
    public function generateSignedQrPayload(Booking $booking, int $validityHours = 12): array
    {
        $appKey = config('app.key');
        $timestamp = now()->timestamp;
        
        // Calculate expiration: 12 hours after booking slot or created date
        $bookingDateTime = Carbon::parse($booking->booking_date . ' ' . ($booking->slot_time ?? '23:59:59'));
        $expiresAt = $bookingDateTime->copy()->addHours($validityHours)->timestamp;

        $dataToSign = implode('|', [
            $booking->id,
            $booking->booking_code,
            $booking->branch_id,
            $booking->customer_id,
            $expiresAt,
        ]);

        $signature = hash_hmac('sha256', $dataToSign, (string) $appKey);

        return [
            'booking_id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'branch_id' => $booking->branch_id,
            'expires_at' => $expiresAt,
            'sig' => $signature,
            'qr_data_string' => base64_encode(json_encode([
                'id' => $booking->id,
                'code' => $booking->booking_code,
                'exp' => $expiresAt,
                'sig' => $signature,
            ])),
        ];
    }

    /**
     * Verify QR code payload scanned at cashier or by operator.
     *
     * @param string|array $scannedData
     * @return array [bool 'is_valid', ?Booking 'booking', ?string 'error_message']
     */
    public function verifyQrCode(string|array $scannedData): array
    {
        try {
            $parsed = is_string($scannedData) 
                ? json_decode(base64_decode($scannedData), true) 
                : $scannedData;

            if (!$parsed || !isset($parsed['id'], $parsed['code'], $parsed['exp'], $parsed['sig'])) {
                // Fallback: If raw plain booking code was scanned
                if (is_string($scannedData) && strlen($scannedData) < 50) {
                    $booking = Booking::where('booking_code', trim($scannedData))
                        ->orWhere('id', trim($scannedData))
                        ->first();

                    if ($booking) {
                        return [
                            'is_valid' => true,
                            'booking' => $booking,
                            'error_message' => null,
                        ];
                    }
                }

                return [
                    'is_valid' => false,
                    'booking' => null,
                    'error_message' => 'Format QR Code tidak valid atau rusak.',
                ];
            }

            // Check expiration
            if (now()->timestamp > (int) $parsed['exp']) {
                return [
                    'is_valid' => false,
                    'booking' => null,
                    'error_message' => 'QR Code booking telah kedaluwarsa.',
                ];
            }

            $booking = Booking::find($parsed['id']);
            if (!$booking || $booking->booking_code !== $parsed['code']) {
                return [
                    'is_valid' => false,
                    'booking' => null,
                    'error_message' => 'Data booking tidak ditemukan di sistem.',
                ];
            }

            // Reconstruct signature
            $dataToSign = implode('|', [
                $booking->id,
                $booking->booking_code,
                $booking->branch_id,
                $booking->customer_id,
                $parsed['exp'],
            ]);

            $expectedSignature = hash_hmac('sha256', $dataToSign, (string) config('app.key'));

            if (!hash_equals($expectedSignature, $parsed['sig'])) {
                return [
                    'is_valid' => false,
                    'booking' => null,
                    'error_message' => 'Tanda tangan digital QR Code tidak valid / telah dimanipulasi.',
                ];
            }

            return [
                'is_valid' => true,
                'booking' => $booking,
                'error_message' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'is_valid' => false,
                'booking' => null,
                'error_message' => 'Gagal memverifikasi QR: ' . $e->getMessage(),
            ];
        }
    }
}
