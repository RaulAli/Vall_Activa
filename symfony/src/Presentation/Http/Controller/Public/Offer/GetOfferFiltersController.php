<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Public\Offer;

use App\Application\Offer\DTO\OfferPublicFilters;
use App\Application\Offer\Handler\GetOfferFiltersHandler;
use App\Application\Offer\PublicQuery\GetOfferFiltersQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class GetOfferFiltersController extends AbstractController
{
    #[Route('/api/public/offers/filters', name: 'public_offer_filters', methods: ['GET'], priority: 20)]
    public function __invoke(Request $request, GetOfferFiltersHandler $handler): JsonResponse
    {
        $q = $this->stringOrNull($request->query->get('q'));
        $discountType = $this->stringOrNull($request->query->get('discountType'));
        $priceMin = $this->stringOrNull($request->query->get('priceMin'));
        $priceMax = $this->stringOrNull($request->query->get('priceMax'));
        $pointsMin = $this->intOrNull($request->query->get('pointsMin'));
        $pointsMax = $this->intOrNull($request->query->get('pointsMax'));

        $inStock = $request->query->get('inStock');
        $inStockBool = ($inStock === '1' || $inStock === 1 || $inStock === true || $inStock === 'true');

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

        $meta = $handler(new GetOfferFiltersQuery($filters));

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
        [$minLng, $minLat, $maxLng, $maxLat] = $parts;
        return [
            is_numeric($minLng) ? (float) $minLng : null,
            is_numeric($minLat) ? (float) $minLat : null,
            is_numeric($maxLng) ? (float) $maxLng : null,
            is_numeric($maxLat) ? (float) $maxLat : null,
        ];
    }
}
