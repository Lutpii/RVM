<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Carbon\Carbon;

class AuthController extends Controller
{
    protected FonnteService $fonnte;

    public function __construct(FonnteService $fonnte)
    {
        $this->fonnte = $fonnte;
    }

    // Register with email/password
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:100',
            'email'    => 'required_without:phone|email|unique:users,email',
            'phone'    => 'required_without:email|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'password_hash' => Hash::make($request->password),
            'is_verified'   => 0,
            'role'          => 'user',
            'total_points'  => 0,
        ]);

        // Send OTP if phone provided
        if ($request->phone) {
            $this->generateAndSendOtp($user);
        }

        $token = $user->createToken('rvm_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => $request->phone ? 'Registration successful. OTP sent to WhatsApp.' : 'Registration successful.',
            'token'   => $token,
            'user'    => $this->formatUser($user),
        ], 201);
    }

    // Login with email/password
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required_without:phone|email',
            'phone'    => 'required_without:email|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = $request->email
            ? User::where('email', $request->email)->first()
            : User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials.'], 401);
        }

        $token = $user->createToken('rvm_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'token'   => $token,
            'user'    => $this->formatUser($user),
        ]);
    }

    // Send OTP via WhatsApp (Fonnte)
    public function sendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Phone number not found.'], 404);
        }

        $this->generateAndSendOtp($user);

        return response()->json(['success' => true, 'message' => 'OTP sent to your WhatsApp.']);
    }

    // Verify OTP
    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp'   => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user || $user->otp_code !== $request->otp) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP.'], 400);
        }

        if (Carbon::now()->isAfter($user->otp_expires_at)) {
            return response()->json(['success' => false, 'message' => 'OTP has expired.'], 400);
        }

        $user->update(['is_verified' => 1, 'otp_code' => null, 'otp_expires_at' => null]);

        $token = $user->createToken('rvm_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Phone verified successfully.',
            'token'   => $token,
            'user'    => $this->formatUser($user),
        ]);
    }

    // Google OAuth redirect
    public function googleRedirect(): JsonResponse
    {
        $url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
        return response()->json(['success' => true, 'url' => $url]);
    }

    // Google OAuth callback — redirects to frontend SPA with token
    public function googleCallback(Request $request): RedirectResponse
    {
        $frontendUrl = rtrim(config('services.frontend_url'), '/');

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::updateOrCreate(
                ['google_id' => $googleUser->getId()],
                [
                    'name'        => $googleUser->getName(),
                    'email'       => $googleUser->getEmail(),
                    'avatar_url'  => $googleUser->getAvatar(),
                    'is_verified' => 1,
                    'role'        => 'user',
                ]
            );

            $token = $user->createToken('rvm_token')->plainTextToken;
            $userJson = urlencode(json_encode($this->formatUser($user)));

            return redirect("{$frontendUrl}/#/auth/callback?token={$token}&user={$userJson}");
        } catch (\Exception $e) {
            return redirect("{$frontendUrl}/#/login?error=google_auth_failed");
        }
    }

    // Get current user
    public function me(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'user' => $this->formatUser($request->user())]);
    }

    // Logout
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logged out successfully.']);
    }

    // Refresh token
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->tokens()->delete();
        $token = $user->createToken('rvm_token')->plainTextToken;
        return response()->json(['success' => true, 'token' => $token]);
    }

    // Private helpers
    private function generateAndSendOtp(User $user): void
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->update([
            'otp_code'       => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(5),
        ]);

        $message = "🌱 *RVM - Reverse Vending Machine*\n\nYour verification code is:\n*{$otp}*\n\nValid for 5 minutes. Do not share this code.";
        $this->fonnte->send($user->phone, $message);
    }

    private function formatUser(User $user): array
    {
        return [
            'id'           => $user->id,
            'name'         => $user->name,
            'email'        => $user->email,
            'phone'        => $user->phone,
            'avatar_url'   => $user->avatar_url,
            'total_points' => $user->total_points,
            'role'         => $user->role,
            'is_verified'  => $user->is_verified,
            'created_at'   => $user->created_at,
        ];
    }
}
