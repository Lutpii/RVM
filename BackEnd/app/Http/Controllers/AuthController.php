<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\User;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
        // 'unique' only fires against an already-*verified* row — a previous
        // registration that never finished OTP (closed the tab, refresh, etc.)
        // has no other way back in otherwise: there's nothing to log into yet
        // (no verified account) and re-submitting the same form would
        // otherwise be permanently rejected as "already taken". Falling
        // through instead re-sends a fresh OTP to that same unverified row —
        // idempotent register, no client-side "resume" state to go stale.
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:100',
            'email'    => ['required_without:phone', 'email', Rule::unique('users', 'email')->where(fn ($q) => $q->where('is_verified', 1))],
            'phone'    => ['required_without:email', 'string', 'max:20', Rule::unique('users', 'phone')->where(fn ($q) => $q->where('is_verified', 1))],
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $existing = $request->email
            ? User::where('email', $request->email)->first()
            : User::where('phone', $request->phone)->first();

        if ($existing) {
            // Same cap as the dedicated resend endpoint (sendOtp() below) —
            // otherwise this path has no rate limit at all and becomes a free
            // way to spam OTPs at someone else's still-unverified email/phone.
            $identifier = $request->email ?: $request->phone;
            $sendKey = 'otp-send:' . $identifier;
            if (RateLimiter::tooManyAttempts($sendKey, 3)) {
                $seconds = RateLimiter::availableIn($sendKey);
                return response()->json(['success' => false, 'message' => __('messages.too_many_otp_requests', ['seconds' => $seconds])], 429);
            }
            RateLimiter::hit($sendKey, 600);

            $existing->update([
                'name'          => $request->name,
                'password_hash' => Hash::make($request->password),
            ]);
            $user = $existing;
        } else {
            $user = User::create([
                'name'          => $request->name,
                'email'         => $request->email,
                'phone'         => $request->phone,
                'password_hash' => Hash::make($request->password),
                'is_verified'   => 0,
                'role'          => 'user',
                'total_points'  => 0,
            ]);
        }

        // Send OTP — via WhatsApp if a phone was given, otherwise via email (at
        // least one of the two is always present per the validation above).
        $this->generateAndSendOtp($user);

        // No token here on purpose — OTP verification is mandatory before an
        // account is usable at all. verifyOtp() is the only place a fresh
        // registration gets a session (see also login()'s is_verified gate,
        // which enforces the same rule for someone re-attempting to log in
        // before verifying).
        return response()->json([
            'success' => true,
            'message' => __('messages.register_success_otp_sent'),
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
            return response()->json(['success' => false, 'message' => __('messages.too_many_login_attempts', ['seconds' => $seconds])], 429);
        }

        $user = $request->email
            ? User::where('email', $request->email)->first()
            : User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            RateLimiter::hit($throttleKey, 60);
            return response()->json(['success' => false, 'message' => __('messages.invalid_credentials')], 401);
        }

        RateLimiter::clear($throttleKey);

        // OTP verification is mandatory — a correct password on an unverified
        // account proves it's really them, but doesn't grant a session. Resend
        // a fresh OTP (same cap as sendOtp()'s own resend endpoint) so they can
        // finish verifying right from the login screen instead of getting stuck.
        if (!$user->is_verified) {
            $identifier = $user->email ?: $user->phone;
            $sendKey = 'otp-send:' . $identifier;
            if (!RateLimiter::tooManyAttempts($sendKey, 3)) {
                RateLimiter::hit($sendKey, 600);
                $this->generateAndSendOtp($user);
            }

            return response()->json([
                'success'            => false,
                'needs_verification' => true,
                'message'            => __('messages.account_not_verified'),
            ], 403);
        }

        $token = $user->createToken('rvm_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => __('messages.login_success'),
            'token'   => $token,
            'user'    => $this->formatUser($user),
        ]);
    }

    // Resend OTP — via WhatsApp (Fonnte) if the account has a phone, otherwise
    // via email. Accepts whichever identifier the account was registered with.
    public function sendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required_without:email|string',
            'email' => 'required_without:phone|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Cap how often OTPs can be requested for one account — otherwise anyone
        // can spam a victim's WhatsApp/inbox (cost + annoyance) with unlimited sends.
        $identifier = $request->phone ?: $request->email;
        $sendKey = 'otp-send:' . $identifier;
        if (RateLimiter::tooManyAttempts($sendKey, 3)) {
            $seconds = RateLimiter::availableIn($sendKey);
            return response()->json(['success' => false, 'message' => __('messages.too_many_otp_requests', ['seconds' => $seconds])], 429);
        }

        $user = $request->phone
            ? User::where('phone', $request->phone)->first()
            : User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => __('messages.account_not_found')], 404);
        }

        RateLimiter::hit($sendKey, 600);
        $this->generateAndSendOtp($user);

        return response()->json(['success' => true, 'message' => __('messages.otp_sent')]);
    }

    // Verify OTP — same dual phone/email identifier as sendOtp() above.
    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required_without:email|string',
            'email' => 'required_without:phone|email',
            'otp'   => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Per-account lockout on the 6-digit code — the 5-minute validity window
        // alone is not enough protection against a distributed brute force.
        $identifier = $request->phone ?: $request->email;
        $verifyKey = 'otp-verify:' . $identifier;
        if (RateLimiter::tooManyAttempts($verifyKey, 5)) {
            $seconds = RateLimiter::availableIn($verifyKey);
            return response()->json(['success' => false, 'message' => __('messages.too_many_otp_attempts', ['seconds' => $seconds])], 429);
        }

        $user = $request->phone
            ? User::where('phone', $request->phone)->first()
            : User::where('email', $request->email)->first();

        // hash_equals() for constant-time comparison — otp_code is never null-safe
        // here (cast to string) since hash_equals() rejects a null needle/haystack.
        if (!$user || !hash_equals((string) $user->otp_code, (string) $request->otp)) {
            RateLimiter::hit($verifyKey, 300);
            return response()->json(['success' => false, 'message' => __('messages.invalid_otp')], 400);
        }

        if (Carbon::now()->isAfter($user->otp_expires_at)) {
            return response()->json(['success' => false, 'message' => __('messages.otp_expired')], 400);
        }

        RateLimiter::clear($verifyKey);
        $user->update(['is_verified' => 1, 'otp_code' => null, 'otp_expires_at' => null]);

        $token = $user->createToken('rvm_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => __('messages.account_verified'),
            'token'   => $token,
            'user'    => $this->formatUser($user),
        ]);
    }

    // Step 1/2: called via the frontend's proxied axios client. The Vite dev
    // proxy rewrites the Host header to its own backend target, so
    // $request->root() here would reflect that internal target (127.0.0.1),
    // not an address the caller can actually reach — wrong for anyone not on
    // the same machine, e.g. a phone on the same hotspot as a LAN-IP-hosted
    // frontend. The frontend passes its own window.location.hostname, which
    // it always knows correctly regardless of the proxy; this server's own
    // real port (config, not inferred) is combined with it. Falls back to
    // $request->root() when no host is given (e.g. direct/non-proxied calls).
    public function googleRedirect(Request $request): JsonResponse
    {
        $root = $this->resolveBackendRoot($request, $request->query('host'));

        // Carried through to googleStart() as a query param (a fresh top-level
        // navigation to a different host — nothing here survives except what's
        // in the URL) so googleCallback() can send the browser back to
        // whichever device/IP actually started the login, instead of a single
        // hardcoded FRONTEND_URL that breaks the moment testing moves from
        // localhost to a phone on the hotspot (or the hotspot's IP changes).
        $frontend = $this->sanitizeFrontendOrigin($request->query('frontend'));
        $url = $root . '/auth/google/start' . ($frontend ? '?frontend=' . urlencode($frontend) : '');

        return response()->json(['success' => true, 'url' => $url]);
    }

    // Only a private-network/localhost origin is ever honored — this value
    // ends up as an unauthenticated redirect target carrying a live OAuth
    // exchange code, so accepting an arbitrary caller-supplied origin here
    // would be an open redirect that hands a real login code to any site an
    // attacker names. Local dev/LAN testing never needs anything outside
    // this range; a real public deployment sets FRONTEND_URL and never
    // reaches this at all (the frontend only sends this param over http/https
    // to begin with, both covered below).
    private function sanitizeFrontendOrigin(?string $origin): ?string
    {
        if (!$origin) {
            return null;
        }
        $pattern = '#^https?://(localhost|127\.0\.0\.1|10\.\d{1,3}\.\d{1,3}\.\d{1,3}|172\.(1[6-9]|2\d|3[01])\.\d{1,3}\.\d{1,3}|192\.168\.\d{1,3}\.\d{1,3})(:\d{1,5})?$#i';
        return preg_match($pattern, $origin) ? $origin : null;
    }

    // Single source of truth for "what address can the caller use to reach
    // this backend directly" — shared by googleRedirect(), googleStart(), and
    // googleCallback() so all three agree on the same host.
    //
    // Priority:
    // 1. PUBLIC_BACKEND_URL (e.g. an ngrok tunnel) — required whenever the
    //    caller is a phone on a private IP: Google's OAuth flatly rejects a
    //    redirect_uri on a private/LAN address ("device_id and device_name
    //    are required for private IP"), so nothing inferred from the request
    //    can ever satisfy Google in that case — only a real public HTTPS
    //    tunnel does.
    // 2. $host (a hostname the frontend told us, e.g. window.location.hostname
    //    when called through the Vite proxy) + this server's own configured
    //    port — for a direct LAN IP with no tunnel (works for anything except
    //    Google login itself, per the above).
    // 3. $request->root() — plain localhost, single machine, nothing proxied.
    private function resolveBackendRoot(Request $request, ?string $host = null): string
    {
        if ($publicUrl = config('services.public_backend_url')) {
            return rtrim($publicUrl, '/');
        }
        if ($host) {
            // php artisan serve only ever serves plain HTTP, regardless of
            // whether the (proxied) frontend is HTTPS.
            return 'http://' . $host . ':' . config('services.backend_port');
        }
        return $request->root();
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
        // frontend already validated in googleRedirect() — re-checked here too
        // since this URL is otherwise just as guessable as any other GET route.
        $frontend = $this->sanitizeFrontendOrigin($request->query('frontend'))
            ?? config('services.frontend_url');
        Cache::put("oauth_state:{$state}", ['nonce' => $nonce, 'frontend' => $frontend], now()->addMinutes(10));

        // Must match what googleCallback() below computes, or the token
        // exchange rejects it as a mismatch. Whatever this resolves to must
        // still be pre-registered as an authorized redirect URI in the
        // Google Cloud Console project.
        $redirectUri = $this->resolveBackendRoot($request) . '/auth/google/callback';
        // Without 'prompt' => 'select_account', Google silently reuses whichever
        // account is already active in the browser instead of asking — fine with
        // one Google session, confusing/wrong the moment someone has more than one.
        $url = Socialite::driver('google')->stateless()->with(['state' => $state, 'prompt' => 'select_account'])->redirectUrl($redirectUri)->redirect()->getTargetUrl();

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
        $state       = $request->query('state');
        $stored      = $state ? Cache::pull("oauth_state:{$state}") : null;
        // $stored is null whenever the state already expired/was consumed —
        // config('services.frontend_url') is the only place left to send the
        // error redirect in that case, since there's nothing else to read it from.
        $frontendUrl   = rtrim($stored['frontend'] ?? config('services.frontend_url'), '/');
        $expectedNonce = $stored['nonce'] ?? null;
        $cookieNonce   = $request->cookie('oauth_nonce');

        if (!$state || !$expectedNonce || !$cookieNonce || !hash_equals($expectedNonce, $cookieNonce)) {
            Log::warning('[GoogleAuth] nonce check failed', [
                'has_state' => (bool) $state, 'has_expected' => (bool) $expectedNonce,
                'has_cookie' => (bool) $cookieNonce,
                'match' => ($expectedNonce && $cookieNonce) ? hash_equals($expectedNonce, $cookieNonce) : null,
            ]);
            return redirect("{$frontendUrl}/#/login?error=google_auth_failed")->withoutCookie('oauth_nonce');
        }

        try {
            // Must match the redirectUrl googleStart() sent to Google exactly.
            $redirectUri = $this->resolveBackendRoot($request) . '/auth/google/callback';

            // This machine's HTTP_PROXY/HTTPS_PROXY env vars point at a dead
            // local proxy (127.0.0.1:9) on at least some local dev setups —
            // Guzzle honors those by default, which breaks the outbound
            // token-exchange/userinfo calls below with "Failed to connect to
            // 127.0.0.1 port 9". This app has no legitimate reason to proxy
            // its own calls to Google, so disable it here regardless of
            // whatever the host environment happens to have set.
            $googleUser = Socialite::driver('google')->stateless()->redirectUrl($redirectUri)
                ->setHttpClient(new \GuzzleHttp\Client(['proxy' => false]))
                ->user();

            // Plain updateOrCreate() would overwrite every listed column on the
            // existing row on every login — including 'role', wiping out an
            // admin promotion the moment that user logs in via Google again.
            // 'role' is set explicitly only for a brand-new row instead: relying
            // on the users.role column's own DB default ('user') would leave it
            // unset on this in-memory $user (Eloquent doesn't re-fetch DB-side
            // defaults after insert — same footgun as User::guest() below).
            $user = User::firstOrNew(['google_id' => $googleUser->getId()]);
            if (!$user->exists) {
                $user->role = 'user';
            }
            $user->fill([
                'name'        => $googleUser->getName(),
                'email'       => $googleUser->getEmail(),
                'avatar_url'  => $googleUser->getAvatar(),
                'is_verified' => 1,
            ]);
            $user->save();

            $token = $user->createToken('rvm_token')->plainTextToken;

            $code = Str::random(40);
            Cache::put("oauth_code:{$code}", ['token' => $token, 'user' => $this->formatUser($user)], now()->addSeconds(60));

            return redirect("{$frontendUrl}/#/auth/callback?code={$code}")->withoutCookie('oauth_nonce');
        } catch (\Exception $e) {
            Log::warning('[GoogleAuth] exception: ' . $e->getMessage());
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
            return response()->json(['success' => false, 'message' => __('messages.invalid_request')], 422);
        }

        $payload = Cache::pull("oauth_code:{$request->code}");
        if (!$payload) {
            return response()->json(['success' => false, 'message' => __('messages.oauth_code_expired')], 400);
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
        return response()->json(['success' => true, 'message' => __('messages.logout_success')]);
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
    // WhatsApp (Fonnte) if the account has a phone — that path was already live
    // and costs nothing extra to keep. Email otherwise, since Resend (already
    // wired up for admin reports) has a free tier and needs no new service.
    private function generateAndSendOtp(User $user): void
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->update([
            'otp_code'       => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(5),
        ]);

        if ($user->phone) {
            $message = "🌱 *RVM - Reverse Vending Machine*\n\nYour verification code is:\n*{$otp}*\n\nValid for 5 minutes. Do not share this code.";
            $this->fonnte->send($user->phone, $message);
        } else {
            Mail::to($user->email)->send(new OtpMail($otp, $user->name));
        }
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
            // Google-only accounts never had a password set — the frontend
            // uses this to hide the "Change Password" form for them instead
            // of showing a field ("Current Password") they can never fill in.
            'has_password' => !empty($user->password_hash),
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
