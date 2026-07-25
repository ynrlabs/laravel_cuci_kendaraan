<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Requests\Auth\SocialLoginRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /**
     * Register a new customer
     */
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        // Create customer profile
        Customer::create([
            'user_id' => $user->id,
            'total_visits' => 0,
            'total_spent' => 0,
            'loyalty_tier' => \App\Enums\LoyaltyTier::BRONZE,
        ]);

        // Generate token
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'user' => $this->formatUser($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Login customer
     */
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        if (!Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
        ], $validated['remember'] ?? false)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial tidak valid.'],
            ]);
        }

        $user = Auth::user();

        // Check if user is active
        if (!$user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ['Akun Anda telah dinonaktifkan.'],
            ]);
        }

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Generate token
        $token = $user->createToken('api-token')->plainTextToken;

        // If branch_id provided, check if user has role in that branch
        if (!empty($validated['branch_id'])) {
            $hasRole = $user->userRoles()
                ->where('branch_id', $validated['branch_id'])
                ->exists();
            
            if (!$hasRole) {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses ke cabang ini.',
                ], 403);
            }
        }

        return response()->json([
            'message' => 'Login berhasil.',
            'user' => $this->formatUser($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Logout customer (revoke token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * Forgot password - send reset link
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $validated = $request->validated();

        $status = Password::sendResetLink($validated);

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Link reset password telah dikirim ke email Anda.',
            ]);
        }

        return response()->json([
            'message' => 'Gagal mengirim link reset password.',
        ], 500);
    }

    /**
     * Reset password
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $validated = $request->validated();

        $status = Password::reset(
            $validated,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password berhasil direset.',
            ]);
        }

        return response()->json([
            'message' => 'Token reset password tidak valid atau sudah kadaluarsa.',
        ], 400);
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(VerifyOtpRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('phone', $validated['phone'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'Nomor telepon tidak ditemukan.',
            ], 404);
        }

        // In a real implementation, you would verify OTP against a stored OTP
        // For now, we'll simulate by checking if OTP is "123456" (for testing)
        // You should implement proper OTP storage and verification
        $storedOtp = cache("otp_{$validated['phone']}");

        if ($storedOtp !== $validated['otp']) {
            return response()->json([
                'message' => 'Kode OTP tidak valid atau sudah kadaluarsa.',
            ], 400);
        }

        // Mark phone as verified
        $user->update(['phone_verified_at' => now()]);

        // Clear OTP cache
        cache()->forget("otp_{$validated['phone']}");

        // Generate token
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Verifikasi OTP berhasil.',
            'user' => $this->formatUser($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Send OTP for phone verification
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:20', 'exists:users,phone'],
        ]);

        $phone = $request->phone;
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in cache for 5 minutes
        cache(["otp_{$phone}" => $otp], 300);

        // In production, send OTP via SMS (Twilio, etc.)
        // For now, return OTP in response for testing
        return response()->json([
            'message' => 'Kode OTP telah dikirim.',
            'otp' => $otp, // Remove in production!
        ]);
    }

    /**
     * Social login (Google/Apple)
     */
    public function socialLogin(SocialLoginRequest $request)
    {
        $validated = $request->validated();

        // In a real implementation, you would verify the token with Google/Apple
        // and get user info (email, name, etc.)
        // For now, we'll simulate the response
        
        $provider = $validated['provider'];
        $token = $validated['token'];

        // TODO: Implement actual social login verification
        // Example for Google:
        // $googleUser = $this->verifyGoogleToken($token);
        // $user = User::updateOrCreate(
        //     ['email' => $googleUser['email']],
        //     ['name' => $googleUser['name'], 'password' => Hash::make(Str::random(32))]
        // );

        // Simulate social user data
        $socialUser = [
            'email' => 'social_' . $provider . '_user@example.com',
            'name' => 'Social User',
        ];

        $user = User::firstOrCreate(
            ['email' => $socialUser['email']],
            [
                'name' => $socialUser['name'],
                'password' => Hash::make(Str::random(32)),
                'phone' => null,
                'is_active' => true,
            ]
        );

        // Create customer profile if not exists
        Customer::firstOrCreate(
            ['user_id' => $user->id],
            [
                'total_visits' => 0,
                'total_spent' => 0,
                'loyalty_tier' => \App\Enums\LoyaltyTier::BRONZE,
            ]
        );

        $apiToken = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login dengan ' . ucfirst($provider) . ' berhasil.',
            'user' => $this->formatUser($user),
            'token' => $apiToken,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Get authenticated user profile
     */
    public function me(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'user' => $this->formatUser($user),
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();

        $user->update($validated);

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user' => $this->formatUser($user),
        ]);
    }

    /**
     * Format user response
     */
    private function formatUser(User $user): array
    {
        $user->load(['customer', 'userRoles.branch']);
        
        $customer = $user->customer;
        $roles = $user->userRoles->map(function ($role) {
            return [
                'role' => $role->role,
                'branch_id' => $role->branch_id,
                'branch' => $role->branch ? [
                    'id' => $role->branch->id,
                    'name' => $role->branch->name,
                ] : null,
            ];
        });

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->avatar,
            'email_verified_at' => $user->email_verified_at,
            'phone_verified_at' => $user->phone_verified_at,
            'is_active' => $user->is_active,
            'last_login_at' => $user->last_login_at,
            'customer' => $customer ? [
                'id' => $customer->id,
                'total_visits' => $customer->total_visits,
                'total_spent' => $customer->total_spent,
                'loyalty_tier' => $customer->loyalty_tier?->value,
                'preferred_branch_id' => $customer->preferred_branch_id,
                'reward_points' => $customer->reward_points,
                'avg_duration' => $customer->avg_duration,
                'satisfaction' => $customer->satisfaction,
            ] : null,
            'roles' => $roles,
        ];
    }
}