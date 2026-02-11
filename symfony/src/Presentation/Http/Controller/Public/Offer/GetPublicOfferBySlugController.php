<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Public\Offer;

use App\Application\Offer\Handler\GetPublicOfferBySlugHandler;
use App\Application\Offer\PublicQuery\GetPublicOfferBySlugQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class GetPublicOfferBySlugController extends AbstractController
{
    #[Route('/api/public/offers/{slug}', name: 'public_offer_details', methods: ['GET'])]
    public function __invoke(string $slug, GetPublicOfferBySlugHandler $handler): JsonResponse
    {
        $offer = $handler(new GetPublicOfferBySlugQuery(slug: $slug));

        if ($offer === null) {
            return $this->json(
                ['error' => 'not_found', 'message' => 'Offer not found'],
                404
            );
        }

        return $this->json($offer);
    }
}
