<?php
// BackEnd/tests/Feature/RewardItemAvailabilityTest.php
namespace Tests\Feature;

use App\Models\RewardItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RewardItemAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private function makeItem(array $overrides = []): RewardItem
    {
        return RewardItem::create(array_merge([
            'name' => 'Test Reward',
            'points_cost' => 10,
            'is_active' => true,
        ], $overrides));
    }

    public function test_active_item_with_no_stock_or_date_limits_is_available(): void
    {
        $item = $this->makeItem();
        $this->assertTrue($item->isAvailable());
    }

    public function test_inactive_item_is_never_available(): void
    {
        $item = $this->makeItem(['is_active' => false]);
        $this->assertFalse($item->isAvailable());
    }

    public function test_item_with_zero_stock_is_unavailable(): void
    {
        $item = $this->makeItem(['stock' => 0]);
        $this->assertFalse($item->isAvailable());
    }

    public function test_item_with_positive_stock_is_available(): void
    {
        $item = $this->makeItem(['stock' => 3]);
        $this->assertTrue($item->isAvailable());
    }

    public function test_item_before_its_valid_from_date_is_unavailable(): void
    {
        $item = $this->makeItem(['valid_from' => now()->addDay()]);
        $this->assertFalse($item->isAvailable());
    }

    public function test_item_after_its_valid_until_date_is_unavailable(): void
    {
        $item = $this->makeItem(['valid_until' => now()->subDay()]);
        $this->assertFalse($item->isAvailable());
    }

    public function test_item_within_its_date_window_is_available(): void
    {
        $item = $this->makeItem(['valid_from' => now()->subDay(), 'valid_until' => now()->addDay()]);
        $this->assertTrue($item->isAvailable());
    }
}
