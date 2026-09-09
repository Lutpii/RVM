<?php

namespace Tests\Unit;

use App\Services\CarbonService;
use PHPUnit\Framework\TestCase;

class CarbonServiceTest extends TestCase
{
    public function test_returns_the_fixed_value_for_each_known_material(): void
    {
        $this->assertSame(0.226, CarbonService::forMaterial('aluminum'));
        $this->assertSame(0.038, CarbonService::forMaterial('plastic'));
        $this->assertSame(0.15, CarbonService::forMaterial('glass'));
        $this->assertSame(0.04, CarbonService::forMaterial('paper'));
    }

    public function test_returns_zero_for_unknown_or_missing_material(): void
    {
        $this->assertSame(0.0, CarbonService::forMaterial('unknown'));
        $this->assertSame(0.0, CarbonService::forMaterial(null));
        $this->assertSame(0.0, CarbonService::forMaterial('banana'));
    }
}
