<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Public\Route;

use App\Application\Route\Handler\GetPublicRouteBySlugHandler;
use App\Application\Route\PublicQuery\GetPublicRouteBySlugQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class GetPublicRouteBySlugController extends AbstractController
{
    #[Route('/api/public/routes/{slug}', name: 'public_route_details', methods: ['GET'])]
    public function __invoke(string $slug, GetPublicRouteBySlugHandler $handler): JsonResponse
    {
        $route = $handler(new GetPublicRouteBySlugQuery(slug: $slug));

        if ($route === null) {
            return $this->json(['error' => 'not_found', 'message' => 'Route not found'], 404);
        }

        return $this->json($route);
    }
}
