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
        $sportCode = is_string($sportCode) && trim($sportCode) !== '' ? mb_strtolower(trim($sportCode)) : null;

        $distanceMin = $this->intOrNull($request->query->get('distanceMin'));
        $distanceMax = $this->intOrNull($request->query->get('distanceMax'));
        $gainMin = $this->intOrNull($request->query->get('gainMin'));
        $gainMax = $this->intOrNull($request->query->get('gainMax'));

        $difficulty = $request->query->get('difficulty');
        $difficulty = is_string($difficulty) && trim($difficulty) !== '' ? strtoupper(trim($difficulty)) : null;

        $routeType = $request->query->get('routeType');
        $routeType = is_string($routeType) && trim($routeType) !== '' ? strtoupper(trim($routeType)) : null;

        $durationMin = $this->intOrNull($request->query->get('durationMin'));
        $durationMax = $this->intOrNull($request->query->get('durationMax'));

        $q = $request->query->get('q');
        $q = is_string($q) && trim($q) !== '' ? trim($q) : null;

        // focus=lng,lat,radiusM (si existe, IGNORA bbox)
        [$focusLng, $focusLat, $focusRadiusM] = $this->parseFocus($request->query->get('focus'));

        // bbox=minLng,minLat,maxLng,maxLat
        [$bboxMinLng, $bboxMinLat, $bboxMaxLng, $bboxMaxLat] = $this->parseBbox($request->query->get('bbox'));

        // Si focus está activo, deshabilitamos bbox (para que mover mapa no cambie)
        if ($focusLng !== null && $focusLat !== null && $focusRadiusM !== null) {
            $bboxMinLng = $bboxMinLat = $bboxMaxLng = $bboxMaxLat = null;
        }

        // Seguridad: si no hay focus ni bbox, devolvemos 400 (evita “todo el mundo”)
        if ($focusLng === null && $bboxMinLng === null) {
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
            bboxMinLng: $bboxMinLng,
            bboxMinLat: $bboxMinLat,
            bboxMaxLng: $bboxMaxLng,
            bboxMaxLat: $bboxMaxLat,
            focusLng: $focusLng,
            focusLat: $focusLat,
            focusRadiusM: $focusRadiusM,
            q: $q,
            difficulty: $difficulty,
            routeType: $routeType,
            durationMin: $durationMin,
            durationMax: $durationMax,
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
        if ($v === null || $v === '')
            return null;
        return is_numeric($v) ? (int) $v : null;
    }

    /**
     * bbox en orden: minLng,minLat,maxLng,maxLat
     * @return array{0:?float,1:?float,2:?float,3:?float}
     */
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
        if (!is_string($focus) || trim($focus) === '') {
            return [null, null, null];
        }

        $parts = array_map('trim', explode(',', $focus));
        if (count($parts) !== 3) {
            return [null, null, null];
        }

        if (!is_numeric($parts[0]) || !is_numeric($parts[1]) || !is_numeric($parts[2])) {
            return [null, null, null];
        }

        $lng = (float) $parts[0];
        $lat = (float) $parts[1];
        $radius = (int) $parts[2];

        if ($radius <= 0) {
            return [null, null, null];
        }

        // Hard caps razonables (evita abuse)
        if ($radius > 2000) {
            $radius = 2000;
        }

        return [$lng, $lat, $radius];
    }
}
