<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Public\Route;

use App\Application\Route\DTO\RoutePublicFilters;
use App\Application\Route\Handler\ListPublicRoutesHandler;
use App\Application\Route\PublicQuery\ListPublicRoutesQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ListPublicRoutesController extends AbstractController
{
    #[Route('/api/public/routes', name: 'public_list_routes', methods: ['GET'])]
    public function __invoke(Request $request, ListPublicRoutesHandler $handler): JsonResponse
    {
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 20);

        $sort = (string) $request->query->get('sort', 'recent');
        $order = (string) $request->query->get('order', 'desc');

        $sportCode = $request->query->get('sportCode');
        $sportCode = is_string($sportCode) && trim($sportCode) !== '' ? trim($sportCode) : null;

        $distanceMin = $this->intOrNull($request->query->get('distanceMin'));
        $distanceMax = $this->intOrNull($request->query->get('distanceMax'));
        $gainMin = $this->intOrNull($request->query->get('gainMin'));
        $gainMax = $this->intOrNull($request->query->get('gainMax'));

        $q = $request->query->get('q');
        $q = is_string($q) && trim($q) !== '' ? trim($q) : null;

        [$bboxMinLng, $bboxMinLat, $bboxMaxLng, $bboxMaxLat] = $this->parseBbox($request->query->get('bbox'));

        $filters = new RoutePublicFilters(
            sportCode: $sportCode,
            distanceMin: $distanceMin,
            distanceMax: $distanceMax,
            gainMin: $gainMin,
            gainMax: $gainMax,
            bboxMinLng: $bboxMinLng,
            bboxMinLat: $bboxMinLat,
            bboxMaxLng: $bboxMaxLng,
            bboxMaxLat: $bboxMaxLat,
            q: $q
        );

        $result = $handler(new ListPublicRoutesQuery(
            filters: $filters,
            page: $page,
            limit: $limit,
            sort: $sort,
            order: $order
        ));

        return $this->json($result);
    }

    private function intOrNull(mixed $v): ?int
    {
        if ($v === null || $v === '') {
            return null;
        }
        if (is_numeric($v)) {
            return (int) $v;
        }
        return null;
    }

    /**
     * bbox en orden: minLng,minLat,maxLng,maxLat
     * @return array{0:?float,1:?float,2:?float,3:?float}
     */
    private function parseBbox(mixed $bbox): array
    {
        if (!is_string($bbox) || trim($bbox) === '') {
            return [null, null, null, null];
        }

        $parts = array_map('trim', explode(',', $bbox));
        if (count($parts) !== 4) {
            return [null, null, null, null];
        }

        if (!is_numeric($parts[0]) || !is_numeric($parts[1]) || !is_numeric($parts[2]) || !is_numeric($parts[3])) {
            return [null, null, null, null];
        }

        $minLng = (float) $parts[0];
        $minLat = (float) $parts[1];
        $maxLng = (float) $parts[2];
        $maxLat = (float) $parts[3];

        // normalización básica: asegurar min<=max
        if ($minLng > $maxLng) {
            [$minLng, $maxLng] = [$maxLng, $minLng];
        }
        if ($minLat > $maxLat) {
            [$minLat, $maxLat] = [$maxLat, $minLat];
        }

        return [$minLng, $minLat, $maxLng, $maxLat];
    }
}
