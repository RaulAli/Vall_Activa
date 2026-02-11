<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Public\Offer;

use App\Application\Offer\DTO\OfferPublicFilters;
use App\Application\Offer\Handler\ListOfferMapBusinessesHandler;
use App\Application\Offer\PublicQuery\ListOfferMapBusinessesQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ListOfferMapBusinessesController extends AbstractController
{
    #[Route('/api/public/offers/map-businesses', name: 'public_offer_map_businesses', methods: ['GET'], priority: 10)]

    public function __invoke(Request $request, ListOfferMapBusinessesHandler $handler): JsonResponse
    {
        $filters = $this->filtersFromRequest($request);
        $items = $handler(new ListOfferMapBusinessesQuery(filters: $filters));

        return $this->json(['items' => $items]);
    }

    private function filtersFromRequest(Request $request): OfferPublicFilters
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

        return new OfferPublicFilters(
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

    /** bbox=minLng,minLat,maxLng,maxLat */
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
