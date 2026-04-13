<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Public\Offer;

use App\Application\Offer\DTO\OfferPublicFilters;
use App\Application\Offer\Handler\ListPublicOffersHandler;
use App\Application\Offer\PublicQuery\ListPublicOffersQuery;
use App\Infrastructure\Ai\NaturalLanguageSearchClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ListPublicOffersController extends AbstractController
{
    #[Route('/api/public/offers', name: 'public_list_offers', methods: ['GET'])]
    public function __invoke(Request $request, ListPublicOffersHandler $handler, NaturalLanguageSearchClient $nlSearch): JsonResponse
    {
        $requestId = $this->resolveRequestId($request);
        $aiUsed = false;
        $aiConfidence = null;

        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 20);
        $sort = (string) $request->query->get('sort', 'recent');
        $order = (string) $request->query->get('order', 'desc');

        $q = $this->stringOrNull($request->query->get('q'));
        $discountType = $this->stringOrNull($request->query->get('discountType'));
        $priceMin = $this->stringOrNull($request->query->get('priceMin'));
        $priceMax = $this->stringOrNull($request->query->get('priceMax'));
        $pointsMin = $this->intOrNull($request->query->get('pointsMin'));
        $pointsMax = $this->intOrNull($request->query->get('pointsMax'));

        $inStock = $request->query->get('inStock');
        $inStockBool = ($inStock === '1' || $inStock === 1 || $inStock === true || $inStock === 'true');

        // Try NLP interpretation, but keep explicit request filters as priority.
        if ($q !== null) {
            $nlDetailed = $nlSearch->interpretDetailed('offers', $q, [
                'sort' => $sort,
                'order' => $order,
            ], $requestId);

            $nlFilters = is_array($nlDetailed) ? ($nlDetailed['filters'] ?? null) : null;
            if (is_array($nlDetailed)) {
                $aiUsed = true;
                $aiConfidence = isset($nlDetailed['confidence']) ? (float) $nlDetailed['confidence'] : null;
            }

            if (is_array($nlFilters)) {
                $discountType = $discountType ?? $this->stringOrNull($nlFilters['discountType'] ?? null);
                $priceMin = $priceMin ?? $this->stringOrNull($nlFilters['priceMin'] ?? null);
                $priceMax = $priceMax ?? $this->stringOrNull($nlFilters['priceMax'] ?? null);
                $pointsMin = $pointsMin ?? $this->intOrNull($nlFilters['pointsMin'] ?? null);
                $pointsMax = $pointsMax ?? $this->intOrNull($nlFilters['pointsMax'] ?? null);

                if (!$inStockBool) {
                    $nlInStock = $nlFilters['inStock'] ?? null;
                    $inStockBool = ($nlInStock === true || $nlInStock === '1' || $nlInStock === 1 || $nlInStock === 'true');
                }

                if ($sort === 'recent') {
                    $sortCandidate = strtolower((string) ($nlFilters['sort'] ?? ''));
                    if (in_array($sortCandidate, ['recent', 'price', 'points'], true)) {
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

        [$bboxMinLng, $bboxMinLat, $bboxMaxLng, $bboxMaxLat] = $this->parseBbox($request->query->get('bbox'));

        $filters = new OfferPublicFilters(
            q: $q,
            discountType: $discountType,
            priceMin: $priceMin,
            priceMax: $priceMax,
            pointsMin: $pointsMin,
            pointsMax: $pointsMax,
            inStock: $inStockBool ? true : null,
            bboxMinLng: $bboxMinLng,
            bboxMinLat: $bboxMinLat,
            bboxMaxLng: $bboxMaxLng,
            bboxMaxLat: $bboxMaxLat
        );

        $result = $handler(new ListPublicOffersQuery(
            filters: $filters,
            page: $page,
            limit: $limit,
            sort: $sort,
            order: $order
        ));

        $response = $this->json($result);
        return $this->withSearchHeaders($response, $requestId, $aiUsed, $aiConfidence);
    }

    private function resolveRequestId(Request $request): string
    {
        $incoming = $request->headers->get('X-Request-Id');
        if (is_string($incoming) && trim($incoming) !== '') {
            return trim($incoming);
        }

        return bin2hex(random_bytes(16));
    }

    private function withSearchHeaders(JsonResponse $response, string $requestId, bool $aiUsed, ?float $confidence): JsonResponse
    {
        $response->headers->set('X-Search-Request-Id', $requestId);
        $response->headers->set('X-Search-Ai-Used', $aiUsed ? '1' : '0');
        if ($confidence !== null) {
            $response->headers->set('X-Search-Ai-Confidence', number_format($confidence, 2, '.', ''));
        }

        return $response;
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

    /** @return array{0:?float,1:?float,2:?float,3:?float} bbox=minLng,minLat,maxLng,maxLat */
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
