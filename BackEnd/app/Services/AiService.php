<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AiService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.ai.url', 'http://localhost:5000');
        $this->apiKey  = config('services.ai.key', '');
    }

    /**
     * Classify material from image path
     * Returns ['material' => 'aluminum', 'confidence' => 0.95]
     */
    public function classify(?string $imagePath): array
    {
        if (!$imagePath) {
            return $this->mockClassify();
        }

        // Only ever accept the exact shape our own capture()/upload code produces
        // (captures/<random>.<ext>) before it's concatenated into a filesystem
        // path. Anything else — "../../../.env", an absolute path, a different
        // directory — is rejected outright rather than passed to storage_path(),
        // which prevents path traversal / arbitrary file read via a crafted
        // image_path.
        if (!preg_match('#^captures/[A-Za-z0-9_-]+\.(jpe?g|png)$#i', $imagePath)) {
            Log::warning("AI: rejected malformed/suspicious image_path: {$imagePath}");
            return $this->mockClassify();
        }

        try {
            $fullPath = storage_path('app/public/' . $imagePath);

            if (!file_exists($fullPath)) {
                Log::warning("AI: Image not found at {$fullPath}");
                return $this->mockClassify();
            }

            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
            ])->attach(
                'image',
                file_get_contents($fullPath),
                basename($fullPath)
            )->post($this->baseUrl . '/classify');

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'material'   => $data['material'] ?? 'unknown',
                    'confidence' => $data['confidence'] ?? 0.0,
                    'all_predictions' => $data['predictions'] ?? [],
                ];
            }

            Log::error("AI Service error: " . $response->body());
            return $this->mockClassify();

        } catch (\Exception $e) {
            Log::error("AI Service exception: " . $e->getMessage());
            return $this->mockClassify();
        }
    }

    /**
     * Trigger a real camera capture on the AI service (Raspberry Pi hardware).
     * Returns the stored image path (on the "public" disk) or null if hardware
     * capture isn't available — callers should fall back to mock/null.
     */
    public function capture(): ?string
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
            ])->post($this->baseUrl . '/capture');

            if (!$response->successful()) {
                return null;
            }

            $filename = 'captures/' . Str::random(20) . '.jpg';
            Storage::disk('public')->put($filename, $response->body());

            return $filename;
        } catch (\Exception $e) {
            Log::error("AI Service capture exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Trigger the sorting servo for a classified material. Fire-and-forget —
     * failures are logged only, never thrown, so a missing/offline AI service
     * can't break the transaction flow.
     */
    public function sort(string $material): void
    {
        try {
            Http::withHeaders([
                'X-API-Key' => $this->apiKey,
            ])->post($this->baseUrl . '/sort', ['material' => $material]);
        } catch (\Exception $e) {
            Log::error("AI Service sort exception: " . $e->getMessage());
        }
    }

    /**
     * 2-bin compactor endpoints (ai_service/machine_api.py). Each returns
     * ['status' => int, 'body' => array], or null if the service can't be
     * reached — never throws, same posture as sort().
     */
    public function state(?string $jobId = null): ?array
    {
        return $this->machineCall('get', '/state', $jobId !== null ? ['job' => $jobId] : []);
    }

    // $capacity: max items of this material per compactor batch (admin's
    // Compactor Settings); null lets the machine use its own default.
    public function deposit(string $material, bool $allowFlush, ?int $capacity = null): ?array
    {
        $payload = ['material' => $material, 'allow_flush' => $allowFlush];
        if ($capacity !== null) {
            $payload['capacity'] = $capacity;
        }
        return $this->machineCall('post', '/deposit', $payload);
    }

    public function flush(): ?array
    {
        return $this->machineCall('post', '/flush');
    }

    public function flapCheck(): ?array
    {
        return $this->machineCall('get', '/flap-check');
    }

    private function machineCall(string $method, string $path, array $payload = []): ?array
    {
        try {
            $response = Http::withHeaders(['X-API-Key' => $this->apiKey])
                ->timeout(5)
                ->{$method}($this->baseUrl . $path, $payload);

            return ['status' => $response->status(), 'body' => $response->json() ?? []];
        } catch (\Exception $e) {
            Log::error("AI Service {$path} exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Mock classification for prototype/testing
     */
    private function mockClassify(): array
    {
        $materials   = ['aluminum', 'plastic', 'glass', 'paper'];
        $randomIndex = array_rand($materials);

        return [
            'material'   => $materials[$randomIndex],
            'confidence' => round(mt_rand(70, 99) / 100, 2),
            'mock'       => true,
        ];
    }
}
