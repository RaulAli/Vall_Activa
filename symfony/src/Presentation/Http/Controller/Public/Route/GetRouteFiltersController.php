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
        $page = 1; // aquí no paginamos, solo meta

        $filters = new RoutePublicFilters(
            sportCode: $this->stringOrNull($request->query->get('sportCode')),
            distanceMin: $this->intOrNull($request->query->get('distanceMin')),
            distanceMax: $this->intOrNull($request->query->get('distanceMax')),
            gainMin: $this->intOrNull($request->query->get('gainMin')),
            gainMax: $this->intOrNull($request->query->get('gainMax')),
            bboxMinLat: null,
            bboxMinLng: null,
            bboxMaxLat: null,
            bboxMaxLng: null,
            q: $this->stringOrNull($request->query->get('q')),
        );

        // bbox=minLng,minLat,maxLng,maxLat
        [$minLng, $minLat, $maxLng, $maxLat] = $this->parseBbox($request->query->get('bbox'));
        $filters = new RoutePublicFilters(
            sportCode: $filters->sportCode,
            distanceMin: $filters->distanceMin,
            distanceMax: $filters->distanceMax,
            gainMin: $filters->gainMin,
            gainMax: $filters->gainMax,
            bboxMinLat: $minLat,
            bboxMinLng: $minLng,
            bboxMaxLat: $maxLat,
            bboxMaxLng: $maxLng,
            q: $filters->q,
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

        if ($minLng > $maxLng) {
            [$minLng, $maxLng] = [$maxLng, $minLng];
        }
        if ($minLat > $maxLat) {
            [$minLat, $maxLat] = [$maxLat, $minLat];
        }

        return [$minLng, $minLat, $maxLng, $maxLat];
    }
}
