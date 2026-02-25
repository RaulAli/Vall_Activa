<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Public\Route;

use App\Application\Route\DTO\RoutePublicFilters;
use App\Application\Route\Handler\GetRouteFiltersHandler;
use App\Application\Route\PublicQuery\GetRouteFiltersQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class GetRouteFiltersController extends AbstractController
{
    #[Route('/api/public/routes/filters', name: 'public_route_filters', methods: ['GET'], priority: 20)]
    public function __invoke(Request $request, GetRouteFiltersHandler $handler): JsonResponse
    {
        $sportCode = $this->stringOrNull($request->query->get('sportCode'));
        $distanceMin = $this->intOrNull($request->query->get('distanceMin'));
        $distanceMax = $this->intOrNull($request->query->get('distanceMax'));
        $gainMin = $this->intOrNull($request->query->get('gainMin'));
        $gainMax = $this->intOrNull($request->query->get('gainMax'));
        $q = $this->stringOrNull($request->query->get('q'));
        $difficulty = $this->stringOrNull($request->query->get('difficulty'));
        $difficulty = $difficulty !== null ? strtoupper($difficulty) : null;
        $routeType = $this->stringOrNull($request->query->get('routeType'));
        $routeType = $routeType !== null ? strtoupper($routeType) : null;
        $durationMin = $this->intOrNull($request->query->get('durationMin'));
        $durationMax = $this->intOrNull($request->query->get('durationMax'));

        // focus tiene prioridad
        [$focusLng, $focusLat, $focusRadiusM] = $this->parseFocus($request->query->get('focus'));

        // bbox=minLng,minLat,maxLng,maxLat
        [$minLng, $minLat, $maxLng, $maxLat] = $this->parseBbox($request->query->get('bbox'));

        if ($focusLng !== null && $focusLat !== null && $focusRadiusM !== null) {
            $minLng = $minLat = $maxLng = $maxLat = null;
        }

        if ($focusLng === null && $minLng === null) {
            return $this->json([
                'error' => 'bad_request',
                'message' => 'Missing bbox or focus parameter.'
            ], 400);
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
            q: $q,
            difficulty: $difficulty,
            routeType: $routeType,
            durationMin: $durationMin,
            durationMax: $durationMax,
        );

        $meta = $handler(new GetRouteFiltersQuery($filters));
        return $this->json($meta);
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
     * focus en orden: lng,lat,radiusM
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
