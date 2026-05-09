<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
