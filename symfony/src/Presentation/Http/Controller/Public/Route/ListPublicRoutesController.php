<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Public\Route;

use App\Application\Route\DTO\RoutePublicFilters;
use App\Application\Route\Handler\ListPublicRoutesHandler;
use App\Application\Route\PublicQuery\ListPublicRoutesQuery;
use App\Infrastructure\Ai\NaturalLanguageSearchClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ListPublicRoutesController extends AbstractController
{
    #[Route('/api/public/routes', name: 'public_list_routes', methods: ['GET'])]
    public function __invoke(Request $request, ListPublicRoutesHandler $handler, NaturalLanguageSearchClient $nlSearch): JsonResponse
    {
        $requestId = $this->resolveRequestId($request);
        $aiUsed = false;
        $aiConfidence = null;

        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 20);

        $sort = (string) $request->query->get('sort', 'recent');
        $order = (string) $request->query->get('order', 'desc');

        $sportCode = $request->query->get('sportCode');
        $sportCode = is_string($sportCode) && trim($sportCode) !== '' ? strtolower(trim($sportCode)) : null;

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
        $guideOnly = $this->boolOrFalse($request->query->get('guideOnly'));

        $q = $request->query->get('q');
        $q = is_string($q) && trim($q) !== '' ? trim($q) : null;

        // Try NLP interpretation, but never break explicit filters from the request.
        if ($q !== null) {
            $nlDetailed = $nlSearch->interpretDetailed('routes', $q, [
                'mode' => 'list',
                'sort' => $sort,
                'order' => $order,
                'sportCode' => $sportCode,
                'difficulty' => $difficulty,
                'routeType' => $routeType,
            ], $requestId);

            $nlFilters = is_array($nlDetailed) ? ($nlDetailed['filters'] ?? null) : null;
            if (is_array($nlDetailed)) {
                $aiUsed = true;
                $aiConfidence = isset($nlDetailed['confidence']) ? (float) $nlDetailed['confidence'] : null;
            }

            if (is_array($nlFilters)) {
                // If AI doesn't return q, drop raw sentence search to avoid over-filtering.
                $q = array_key_exists('q', $nlFilters) ? $this->stringOrNull($nlFilters['q']) : null;

                $sportCode = $sportCode ?? $this->stringOrNull($nlFilters['sportCode'] ?? null);
                $distanceMin = $distanceMin ?? $this->intOrNull($nlFilters['distanceMin'] ?? null);
                $distanceMax = $distanceMax ?? $this->intOrNull($nlFilters['distanceMax'] ?? null);
                $gainMin = $gainMin ?? $this->intOrNull($nlFilters['gainMin'] ?? null);
                $gainMax = $gainMax ?? $this->intOrNull($nlFilters['gainMax'] ?? null);
                $durationMin = $durationMin ?? $this->intOrNull($nlFilters['durationMin'] ?? null);
                $durationMax = $durationMax ?? $this->intOrNull($nlFilters['durationMax'] ?? null);

                if ($difficulty === null) {
                    $difficultyCandidate = $this->stringOrNull($nlFilters['difficulty'] ?? null);
                    $difficulty = $difficultyCandidate !== null ? strtoupper($difficultyCandidate) : null;
                }

                if ($routeType === null) {
                    $routeTypeCandidate = $this->stringOrNull($nlFilters['routeType'] ?? null);
                    $routeType = $routeTypeCandidate !== null ? strtoupper($routeTypeCandidate) : null;
                }

                if ($sort === 'recent') {
                    $sortCandidate = strtolower((string) ($nlFilters['sort'] ?? ''));
                    if (in_array($sortCandidate, ['recent', 'distance', 'gain'], true)) {
                        $sort = $sortCandidate;
                    }
                }

                if ($order === 'desc') {
                    $orderCandidate = strtolower((string) ($nlFilters['order'] ?? ''));
                    if (in_array($orderCandidate, ['asc', 'desc'], true)) {
                        $order = $orderCandidate;
                    }
                }
            }
        }

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
            $response = $this->json([
                'error' => 'bad_request',
                'message' => 'Missing bbox or focus parameter.'
            ], 400);
            return $this->withSearchHeaders($response, $requestId, $aiUsed, $aiConfidence);
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
            guideOnly: $guideOnly,
        );

        $result = $handler(new ListPublicRoutesQuery(
            filters: $filters,
            page: $page,
            limit: $limit,
            sort: $sort,
            order: $order
        ));

        $response = $this->json($result);
        return $this->withSearchHeaders($response, $requestId, $aiUsed, $aiConfidence, [
            'sportCode' => $sportCode,
            'distanceMin' => $distanceMin,
            'distanceMax' => $distanceMax,
            'gainMin' => $gainMin,
            'gainMax' => $gainMax,
            'difficulty' => $difficulty,
            'routeType' => $routeType,
            'durationMin' => $durationMin,
            'durationMax' => $durationMax,
            'guideOnly' => $guideOnly ? '1' : null,
            'sort' => $sort,
            'order' => $order,
        ]);
    }

    private function resolveRequestId(Request $request): string
    {
        $incoming = $request->headers->get('X-Request-Id');
        if (is_string($incoming) && trim($incoming) !== '') {
            return trim($incoming);
        }

        return bin2hex(random_bytes(16));
    }

    private function withSearchHeaders(JsonResponse $response, string $requestId, bool $aiUsed, ?float $confidence, array $appliedFilters = []): JsonResponse
    {
        $response->headers->set('X-Search-Request-Id', $requestId);
        $response->headers->set('X-Search-Ai-Used', $aiUsed ? '1' : '0');
        if ($confidence !== null) {
            $response->headers->set('X-Search-Ai-Confidence', number_format($confidence, 2, '.', ''));
        }

        $headerMap = [
            'sportCode' => 'X-Search-Applied-Sport-Code',
            'distanceMin' => 'X-Search-Applied-Distance-Min',
            'distanceMax' => 'X-Search-Applied-Distance-Max',
            'gainMin' => 'X-Search-Applied-Gain-Min',
            'gainMax' => 'X-Search-Applied-Gain-Max',
            'difficulty' => 'X-Search-Applied-Difficulty',
            'routeType' => 'X-Search-Applied-Route-Type',
            'durationMin' => 'X-Search-Applied-Duration-Min',
            'durationMax' => 'X-Search-Applied-Duration-Max',
            'guideOnly' => 'X-Search-Applied-Guide-Only',
            'sort' => 'X-Search-Applied-Sort',
            'order' => 'X-Search-Applied-Order',
        ];

        foreach ($headerMap as $key => $headerName) {
            if (!array_key_exists($key, $appliedFilters)) {
                continue;
            }

            $value = $appliedFilters[$key];
            if ($value === null || $value === '') {
                continue;
            }

            $response->headers->set($headerName, (string) $value);
        }

        return $response;
    }

    private function intOrNull(mixed $v): ?int
    {
        if ($v === null || $v === '')
            return null;
        return is_numeric($v) ? (int) $v : null;
    }

    private function stringOrNull(mixed $v): ?string
    {
        return (is_string($v) && trim($v) !== '') ? trim($v) : null;
    }

    private function boolOrFalse(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        return false;
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
