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
        $filters = new OfferPublicFilters(
            q: $request->query->get('q'),
            discountType: null,
            priceMin: null,
            priceMax: null,
            pointsMin: null,
            pointsMax: null,
            inStock: null,
            bboxMinLng: null,
            bboxMinLat: null,
            bboxMaxLng: null,
            bboxMaxLat: null
        );

        $meta = $handler(new GetOfferFiltersQuery($filters));

        return $this->json($meta);
    }
}
