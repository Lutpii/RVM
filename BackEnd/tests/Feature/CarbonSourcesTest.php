<?php

namespace Tests\Feature;

use App\Services\CarbonService;
use Tests\TestCase;

class CarbonSourcesTest extends TestCase
{
    public function test_sources_lists_all_materials_with_references(): void
    {
        $response = $this->getJson('/api/carbon/sources')->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('sources.plastic.kg_co2e_per_item', 0.012)
            ->assertJsonPath('sources.plastic.derived', true)
            ->assertJsonPath('sources.paper.kg_co2e_per_item', 0.01)
            ->assertJsonPath('sources.aluminum.kg_co2e_per_item', 0.226)
            ->assertJsonPath('sources.food_waste.kg_co2e_per_kg', 0.44);

        $sources = $response->json('sources');
        $this->assertEqualsCanonicalizing(['aluminum', 'plastic', 'paper', 'glass', 'food_waste'], array_keys($sources));
        foreach ($sources as $source) {
            $this->assertNotEmpty($source['reference']);
        }
        $this->assertStringContainsString('derived from abstract', $sources['plastic']['reference']);
    }

    public function test_for_material_uses_new_values(): void
    {
        $this->assertSame(0.012, CarbonService::forMaterial('plastic'));
        $this->assertSame(0.01, CarbonService::forMaterial('paper'));
    }
}
