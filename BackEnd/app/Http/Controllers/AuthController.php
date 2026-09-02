<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
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
            'password' => 'required|string|min:8|confirmed',
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

        // Per-account lockout, independent of the generic per-IP API throttle —
        // otherwise a distributed attacker (rotating IPs) has no real limit on
        // password guesses against one account.
        $throttleKey = 'login:' . Str::lower($request->email ?? $request->phone);
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json(['success' => false, 'message' => "Too many login attempts. Try again in {$seconds} seconds."], 429);
        }

        $user = $request->email
            ? User::where('email', $request->email)->first()
            : User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            RateLimiter::hit($throttleKey, 60);
            return response()->json(['success' => false, 'message' => 'Invalid credentials.'], 401);
        }

        RateLimiter::clear($throttleKey);
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

        // Cap how often OTPs can be requested for one phone — otherwise anyone can
        // spam a victim's WhatsApp (cost + annoyance) with unlimited sends.
        $sendKey = 'otp-send:' . $request->phone;
        if (RateLimiter::tooManyAttempts($sendKey, 3)) {
            $seconds = RateLimiter::availableIn($sendKey);
            return response()->json(['success' => false, 'message' => "Too many OTP requests. Try again in {$seconds} seconds."], 429);
        }

        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Phone number not found.'], 404);
        }

        RateLimiter::hit($sendKey, 600);
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

        // Per-phone lockout on the 6-digit code — the 5-minute validity window
        // alone is not enough protection against a distributed brute force.
        $verifyKey = 'otp-verify:' . $request->phone;
        if (RateLimiter::tooManyAttempts($verifyKey, 5)) {
            $seconds = RateLimiter::availableIn($verifyKey);
            return response()->json(['success' => false, 'message' => "Too many attempts. Try again in {$seconds} seconds."], 429);
        }

        $user = User::where('phone', $request->phone)->first();

        // hash_equals() for constant-time comparison — otp_code is never null-safe
        // here (cast to string) since hash_equals() rejects a null needle/haystack.
        if (!$user || !hash_equals((string) $user->otp_code, (string) $request->otp)) {
            RateLimiter::hit($verifyKey, 300);
            return response()->json(['success' => false, 'message' => 'Invalid OTP.'], 400);
        }

        if (Carbon::now()->isAfter($user->otp_expires_at)) {
            return response()->json(['success' => false, 'message' => 'OTP has expired.'], 400);
        }

        RateLimiter::clear($verifyKey);
        $user->update(['is_verified' => 1, 'otp_code' => null, 'otp_expires_at' => null]);

        $token = $user->createToken('rvm_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Phone verified successfully.',
            'token'   => $token,
            'user'    => $this->formatUser($user),
        ]);
    }

    // Step 1/2: called via the frontend's proxied axios client, so the browser's
    // apparent host here is the FRONTEND's (e.g. localhost:5173), not this
    // server's real one. Just hands back this server's own real, absolute URL
    // — computed from the request itself, so it's correct in every environment
    // (local, ngrok, production) without hardcoding a host anywhere.
    public function googleRedirect(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'url' => $request->root() . '/auth/google/start']);
    }

    // Step 2/2: reached via a real top-level browser navigation straight to
    // this server's own host (see googleRedirect() above and loginWithGoogle()
    // in the frontend) — NOT through the frontend dev server's proxy. That
    // matters because Google's own redirect back to googleCallback() also
    // lands directly on this same real host, and the nonce cookie set here
    // must be readable there. Setting it via the proxied call instead (the
    // previous version of this fix) scoped the cookie to the frontend's
    // apparent host, which Google's callback never shares — the cookie
    // silently never arrived and every login failed with google_auth_failed.
    public function googleStart(Request $request): RedirectResponse
    {
        // Socialite's own stateless() skips its session-backed state check (this
        // is a pure JSON API with no session middleware on /api), so state/nonce
        // are reimplemented here by hand: state travels with Google's redirect,
        // nonce travels in an httpOnly cookie on this same browser. The callback
        // only proceeds if both come back and match — a request replayed or
        // forged from a different browser won't have the matching cookie.
        $state = Str::random(40);
        $nonce = Str::random(40);
        Cache::put("oauth_state:{$state}", $nonce, now()->addMinutes(10));

        $url = Socialite::driver('google')->stateless()->with(['state' => $state])->redirect()->getTargetUrl();

        // Secure flag follows the actual request scheme — local dev runs plain
        // HTTP (php artisan serve), where a Secure cookie would silently never
        // be stored at all.
        return redirect($url)
            ->cookie('oauth_nonce', $nonce, 10, '/', null, $request->isSecure(), true, false, 'Lax');
    }

    // Google OAuth callback — verifies state/nonce, then hands the frontend a
    // short-lived single-use exchange code instead of the real bearer token, so
    // the token itself never sits in a URL (browser history, server access logs,
    // a shared/bookmarked link) even briefly.
    public function googleCallback(Request $request): RedirectResponse
    {
        $frontendUrl = rtrim(config('services.frontend_url'), '/');

        $state         = $request->query('state');
        $expectedNonce = $state ? Cache::pull("oauth_state:{$state}") : null;
        $cookieNonce   = $request->cookie('oauth_nonce');

        if (!$state || !$expectedNonce || !$cookieNonce || !hash_equals($expectedNonce, $cookieNonce)) {
            return redirect("{$frontendUrl}/#/login?error=google_auth_failed")->withoutCookie('oauth_nonce');
        }

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

            $code = Str::random(40);
            Cache::put("oauth_code:{$code}", ['token' => $token, 'user' => $this->formatUser($user)], now()->addSeconds(60));

            return redirect("{$frontendUrl}/#/auth/callback?code={$code}")->withoutCookie('oauth_nonce');
        } catch (\Exception $e) {
            return redirect("{$frontendUrl}/#/login?error=google_auth_failed")->withoutCookie('oauth_nonce');
        }
    }

    // Redeem the one-time code from googleCallback() for the real token — single
    // use (Cache::pull), short TTL, so even if the code leaked via the redirect
    // URL it's only a live credential for a few seconds and only once.
    public function googleExchange(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), ['code' => 'required|string']);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid request.'], 422);
        }

        $payload = Cache::pull("oauth_code:{$request->code}");
        if (!$payload) {
            return response()->json(['success' => false, 'message' => 'This sign-in link has expired or was already used.'], 400);
        }

        return response()->json(['success' => true, 'token' => $payload['token'], 'user' => $payload['user']]);
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
            'total_carbon_saved' => $this->totalCarbonSavedFor($user),
            'role'         => $user->role,
            'is_verified'  => $user->is_verified,
            'theme_preference' => $user->theme_preference,
            'created_at'   => $user->created_at,
        ];
    }

    private function totalCarbonSavedFor(User $user): float
    {
        $counts = \App\Models\Transaction::where('user_id', $user->id)
            ->where('is_valid', 1)
            ->selectRaw('material_selected, COUNT(*) as count')
            ->groupBy('material_selected')
            ->pluck('count', 'material_selected');

        $total = 0.0;
        foreach ($counts as $material => $count) {
            $total += $count * \App\Services\CarbonService::forMaterial($material);
        }
        return round($total, 3);
    }
}
