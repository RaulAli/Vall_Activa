<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Public\Route;

use App\Application\Route\DTO\RoutePublicFilters;
use App\Application\Route\Handler\ListPublicRouteMapMarkersHandler;
use App\Application\Route\PublicQuery\ListPublicRouteMapMarkersQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ListPublicRouteMapMarkersController extends AbstractController
{
    #[Route('/api/public/routes/map-markers', name: 'public_route_map_markers', methods: ['GET'], priority: 30)]
    public function __invoke(Request $request, ListPublicRouteMapMarkersHandler $handler): JsonResponse
    {
        $sportCode = $this->stringOrNull($request->query->get('sportCode'));
        $distanceMin = $this->intOrNull($request->query->get('distanceMin'));
        $distanceMax = $this->intOrNull($request->query->get('distanceMax'));
        $gainMin = $this->intOrNull($request->query->get('gainMin'));
        $gainMax = $this->intOrNull($request->query->get('gainMax'));
        $q = $this->stringOrNull($request->query->get('q'));

        [$focusLng, $focusLat, $focusRadiusM] = $this->parseFocus($request->query->get('focus'));
        [$minLng, $minLat, $maxLng, $maxLat] = $this->parseBbox($request->query->get('bbox'));

        if ($focusLng !== null && $focusLat !== null && $focusRadiusM !== null) {
            $minLng = $minLat = $maxLng = $maxLat = null;
        }

        if ($focusLng === null && $minLng === null) {
            return $this->json(['error' => 'bad_request', 'message' => 'Missing bbox or focus parameter.'], 400);
        }

        $filters = new RoutePublicFilters(
            sportCode: $sportCode,
            distanceMin: $distanceMin,
            distanceMax: $distanceMax,
            gainMin: $gainMin,
            gainMax: $gainMax,
            bboxMinLng: $minLng,
            bboxMinLat: $minLat,
            bboxMaxLng: $maxLng,
            bboxMaxLat: $maxLat,
            focusLng: $focusLng,
            focusLat: $focusLat,
            focusRadiusM: $focusRadiusM,
            q: $q
        );

        $limit = (int) $request->query->get('limit', 5000);
        if ($limit <= 0)
            $limit = 5000;
        if ($limit > 10000)
            $limit = 10000;

        return $this->json($handler(new ListPublicRouteMapMarkersQuery($filters, $limit)));
    }

    private function stringOrNull(mixed $v): ?string
    {
        return (is_string($v) && trim($v) !== '') ? trim($v) : null;
    }

    private function intOrNull(mixed $v): ?int
    {
        if ($v === null || $v === '')
            return null;
        return is_numeric($v) ? (int) $v : null;
    }

    /** @return array{0:?float,1:?float,2:?float,3:?float} */
    private function parseBbox(mixed $bbox): array
    {
        if (!is_string($bbox) || trim($bbox) === '')
            return [null, null, null, null];

        $parts = array_map('trim', explode(',', $bbox));
        if (count($parts) !== 4)
            return [null, null, null, null];
        if (!is_numeric($parts[0]) || !is_numeric($parts[1]) || !is_numeric($parts[2]) || !is_numeric($parts[3])) {
            return [null, null, null, null];
        }

        $minLng = (float) $parts[0];
        $minLat = (float) $parts[1];
        $maxLng = (float) $parts[2];
        $maxLat = (float) $parts[3];

        if ($minLng > $maxLng)
            [$minLng, $maxLng] = [$maxLng, $minLng];
        if ($minLat > $maxLat)
            [$minLat, $maxLat] = [$maxLat, $minLat];

        return [$minLng, $minLat, $maxLng, $maxLat];
    }

    /**
     * focus: lng,lat,radiusM
     * @return array{0:?float,1:?float,2:?int}
     */
    private function parseFocus(mixed $focus): array
    {
        if (!is_string($focus) || trim($focus) === '')
            return [null, null, null];

        $parts = array_map('trim', explode(',', $focus));
        if (count($parts) !== 3)
            return [null, null, null];

        if (!is_numeric($parts[0]) || !is_numeric($parts[1]) || !is_numeric($parts[2])) {
            return [null, null, null];
        }

        $lng = (float) $parts[0];
        $lat = (float) $parts[1];
        $radius = (int) $parts[2];

        if ($radius <= 0)
            return [null, null, null];
        if ($radius > 2000)
            $radius = 2000;

        return [$lng, $lat, $radius];
    }
}
