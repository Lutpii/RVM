<?php

namespace Tests\Unit;

use App\Services\GoogleMapsLinkService;
use PHPUnit\Framework\TestCase;

class GoogleMapsLinkParseTest extends TestCase
{
    private const PLACE_URL = 'https://www.google.com/maps/place/Faculty+of+Electrical+%26+Electronics+Engineering+Technology/@3.5398981,103.4293861,18.22z/data=!4m6!3m5!1s0x31cf513e4fafa01b:0x9de9f14eb2ba6834!8m2!3d3.5397744!4d103.4301089!16s%2Fg%2F11dym8ykdy?entry=tts';

    public function test_place_link_uses_the_pin_not_the_map_view_and_reads_the_place_name(): void
    {
        $this->assertSame(
            ['latitude' => 3.5397744, 'longitude' => 103.4301089, 'place' => 'Faculty of Electrical & Electronics Engineering Technology'],
            GoogleMapsLinkService::parse(self::PLACE_URL),
        );
    }

    public function test_map_view_link_without_a_pin_uses_the_centre(): void
    {
        $this->assertSame(
            ['latitude' => 3.5398981, 'longitude' => 103.4293861, 'place' => null],
            GoogleMapsLinkService::parse('https://www.google.com/maps/@3.5398981,103.4293861,17z'),
        );
    }

    public function test_query_link_with_coordinates(): void
    {
        $this->assertSame(
            ['latitude' => -6.2, 'longitude' => 106.816666, 'place' => null],
            GoogleMapsLinkService::parse('https://maps.google.com/?q=-6.2,106.816666'),
        );
    }

    public function test_link_without_coordinates_or_out_of_range_gives_null(): void
    {
        $this->assertNull(GoogleMapsLinkService::parse('https://www.google.com/maps/search/coffee'));
        $this->assertNull(GoogleMapsLinkService::parse('https://www.google.com/maps/@95.0,103.4,17z'));
    }
}
