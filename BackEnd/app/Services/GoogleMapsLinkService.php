<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

// Turns a Google Maps link pasted by an admin (short maps.app.goo.gl or a long
// google.com/maps URL) into the pin's coordinates and place name.
// Only Google hosts are ever requested, on every redirect hop, so the server
// cannot be pointed at internal or arbitrary addresses.
class GoogleMapsLinkService
{
    private const HOSTS = ['maps.app.goo.gl', 'goo.gl', 'www.google.com', 'google.com', 'maps.google.com'];
    private const MAX_HOPS = 3;

    /** @return array{latitude: float, longitude: float, place: ?string}|null */
    public function resolve(string $url): ?array
    {
        for ($hop = 0; $hop <= self::MAX_HOPS; $hop++) {
            if (!self::allowed($url)) {
                return null;
            }
            if ($parsed = self::parse($url)) {
                return $parsed;
            }
            if ($hop === self::MAX_HOPS) {
                return null;
            }
            try {
                $response = Http::withoutRedirecting()->timeout(5)->get($url);
            } catch (\Throwable) {
                return null;
            }
            $next = $response->header('Location');
            if (!$response->redirect() || !$next) {
                return null;
            }
            $url = $next;
        }

        return null;
    }

    /** @return array{latitude: float, longitude: float, place: ?string}|null */
    public static function parse(string $url): ?array
    {
        $decoded = urldecode($url);
        $number = '(-?\d{1,3}(?:\.\d+)?)';
        // The pin (!3d lat !4d lng) is more exact than the map view centre (@lat,lng).
        if (!preg_match("/!3d{$number}!4d{$number}/", $decoded, $m)
            && !preg_match("/@{$number},{$number}/", $decoded, $m)
            && !preg_match("/[?&](?:q|ll|query)={$number},\s*{$number}/", $decoded, $m)) {
            return null;
        }
        [$lat, $lng] = [(float) $m[1], (float) $m[2]];
        if (abs($lat) > 90 || abs($lng) > 180) {
            return null;
        }

        $place = preg_match('#/maps/place/([^/@?]+)#', $url, $p)
            ? trim(urldecode(str_replace('+', ' ', $p[1])))
            : null;

        return ['latitude' => $lat, 'longitude' => $lng, 'place' => $place ?: null];
    }

    private static function allowed(string $url): bool
    {
        $parts = parse_url($url);
        return ($parts['scheme'] ?? null) === 'https'
            && in_array(strtolower($parts['host'] ?? ''), self::HOSTS, true);
    }
}
