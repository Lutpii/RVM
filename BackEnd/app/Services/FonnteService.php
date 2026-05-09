<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    private string $token;
    private string $sender;

    public function __construct()
    {
        $this->token  = config('services.fonnte.token', '');
        $this->sender = config('services.fonnte.sender', '');
    }

    public function send(string $phone, string $message): bool
    {
        try {
            // Normalize phone: remove leading 0, add country code 60 (Malaysia)
            $phone = $this->normalizePhone($phone);

            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->post('https://api.fonnte.com/send', [
                'target'  => $phone,
                'message' => $message,
                'countryCode' => '60',
            ]);

            if ($response->successful()) {
                Log::info("Fonnte OTP sent to {$phone}");
                return true;
            }

            Log::error("Fonnte send failed: " . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error("Fonnte exception: " . $e->getMessage());
            return false;
        }
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '60' . substr($phone, 1);
        }
        if (!str_starts_with($phone, '60')) {
            $phone = '60' . $phone;
        }
        return $phone;
    }
}
