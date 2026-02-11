<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Public\Offer;

use App\Application\Offer\Handler\ListPublicOffersHandler;
use App\Application\Offer\PublicQuery\ListPublicOffersQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ListPublicOffersController extends AbstractController
{
    #[Route('/api/public/offers', name: 'public_list_offers', methods: ['GET'])]
    public function __invoke(Request $request, ListPublicOffersHandler $handler): JsonResponse
    {
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 20);

        $result = $handler(new ListPublicOffersQuery(page: $page, limit: $limit));

        return $this->json($result);
    }
}
