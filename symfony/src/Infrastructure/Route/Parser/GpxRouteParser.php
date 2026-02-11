<?php
declare(strict_types=1);

namespace App\Infrastructure\Route\Parser;

use App\Application\Route\DTO\ParsedRouteData;
use App\Application\Route\Port\RouteParserInterface;
use App\Domain\Route\ValueObject\RouteSourceFormat;

final class GpxRouteParser implements RouteParserInterface
{
    public function supports(RouteSourceFormat $format): bool
    {
        return $format === RouteSourceFormat::GPX;
    }

    public function parse(RouteSourceFormat $format, string $absolutePath): ParsedRouteData
    {
        if (!is_file($absolutePath)) {
            throw new \InvalidArgumentException('Source file not found: ' . $absolutePath);
        }

        $xml = @simplexml_load_file($absolutePath);
        if ($xml === false) {
            throw new \InvalidArgumentException('Invalid GPX file.');
        }

        // Namespace handling
        $namespaces = $xml->getNamespaces(true);
        if (isset($namespaces[''])) {
            $xml->registerXPathNamespace('gpx', $namespaces['']);
        } else {
            $xml->registerXPathNamespace('gpx', 'http://www.topografix.com/GPX/1/1');
        }

        $points = $xml->xpath('//gpx:trk//gpx:trkseg//gpx:trkpt');
        if (!$points || count($points) < 2) {
            // fallback sin namespace
            $points = $xml->xpath('//trk//trkseg//trkpt') ?: [];
        }

        if (count($points) < 2) {
            return new ParsedRouteData(null, null, null, null, null, null, null, null, 0, 0, 0, null);
        }

        $minLat = INF;
        $minLng = INF;
        $maxLat = -INF;
        $maxLng = -INF;

        $startLat = null;
        $startLng = null;
        $endLat = null;
        $endLng = null;

        $distance = 0.0;
        $gain = 0.0;
        $loss = 0.0;

        $prevLat = null;
        $prevLng = null;
        $prevEle = null;

        $pointsForPolyline = [];

        foreach ($points as $i => $pt) {
            $lat = (float) $pt['lat'];
            $lng = (float) $pt['lon'];

            $pointsForPolyline[] = [$lat, $lng];

            $ele = null;
            if (isset($pt->ele)) {
                $ele = (float) $pt->ele;
            } else {
                $eleNode = $pt->xpath('gpx:ele');
                if ($eleNode && isset($eleNode[0])) {
                    $ele = (float) $eleNode[0];
                }
            }

            if ($i === 0) {
                $startLat = $lat;
                $startLng = $lng;
            }
            $endLat = $lat;
            $endLng = $lng;

            $minLat = min($minLat, $lat);
            $minLng = min($minLng, $lng);
            $maxLat = max($maxLat, $lat);
            $maxLng = max($maxLng, $lng);

            if ($prevLat !== null && $prevLng !== null) {
                $distance += $this->haversineMeters($prevLat, $prevLng, $lat, $lng);
            }

            if ($prevEle !== null && $ele !== null) {
                $delta = $ele - $prevEle;
                if ($delta > 0) {
                    $gain += $delta;
                } elseif ($delta < 0) {
                    $loss += abs($delta);
                }
            }

            $prevLat = $lat;
            $prevLng = $lng;
            $prevEle = $ele;
        }

        $minLat = is_finite($minLat) ? $minLat : null;
        $minLng = is_finite($minLng) ? $minLng : null;
        $maxLat = is_finite($maxLat) ? $maxLat : null;
        $maxLng = is_finite($maxLng) ? $maxLng : null;

        $polyline = $this->encodePolyline($pointsForPolyline);

        return new ParsedRouteData(
            $startLat,
            $startLng,
            $endLat,
            $endLng,
            $minLat,
            $minLng,
            $maxLat,
            $maxLng,
            (int) round($distance),
            (int) round($gain),
            (int) round($loss),
            $polyline
        );
    }

    private function haversineMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $r = 6371000.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $r * $c;
    }

    private function encodePolyline(array $points): string
    {
        $result = '';
        $prevLat = 0;
        $prevLng = 0;

        foreach ($points as $p) {
            $lat = (float) $p[0];
            $lng = (float) $p[1];

            $latE5 = (int) round($lat * 1e5);
            $lngE5 = (int) round($lng * 1e5);

            $result .= $this->encodeSignedNumber($latE5 - $prevLat);
            $result .= $this->encodeSignedNumber($lngE5 - $prevLng);

            $prevLat = $latE5;
            $prevLng = $lngE5;
        }

        return $result;
    }

    private function encodeSignedNumber(int $num): string
    {
        $num <<= 1;
        if ($num < 0) {
            $num = ~$num;
        }

        $encoded = '';
        while ($num >= 0x20) {
            $encoded .= chr((0x20 | ($num & 0x1f)) + 63);
            $num >>= 5;
        }
        $encoded .= chr($num + 63);

        return $encoded;
    }
}
