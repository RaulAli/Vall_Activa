<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Route;

use App\Infrastructure\Persistence\Doctrine\Entity\Route\RouteOrm;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class UpdateRouteController extends AbstractController
{
    private const VISIBILITY_VALUES = ['PUBLIC', 'UNLISTED', 'PRIVATE'];
    private const STATUS_VALUES = ['DRAFT', 'PUBLISHED', 'ARCHIVED'];

    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    #[Route('/api/routes/{id}', name: 'update_route', methods: ['PATCH'])]
    public function __invoke(string $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $userId = $this->extractUserId($request);
        if ($userId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        // Basic UUID format check
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $id)) {
            return $this->json(['error' => 'invalid_id'], 400);
        }

        $route = $em->getRepository(RouteOrm::class)->find($id);
        if ($route === null) {
            return $this->json(['error' => 'not_found'], 404);
        }
        if ($route->createdByUserId !== $userId) {
            return $this->json(['error' => 'forbidden'], 403);
        }

        /** @var array<string, mixed> $body */
        $body = json_decode($request->getContent(), true) ?? [];

        if (isset($body['visibility']) && in_array($body['visibility'], self::VISIBILITY_VALUES, true)) {
            $route->visibility = (string) $body['visibility'];
        }
        if (isset($body['status']) && in_array($body['status'], self::STATUS_VALUES, true)) {
            $route->status = (string) $body['status'];
        }

        $route->updatedAt = new \DateTimeImmutable();
        $em->flush();

        return $this->json([
            'id' => $route->id,
            'visibility' => $route->visibility,
            'status' => $route->status,
        ]);
    }

    private function extractUserId(Request $request): ?string
    {
        $authHeader = $request->headers->get('Authorization', '');
        if (!str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }
        try {
            $payload = $this->jwtManager->parse(substr($authHeader, 7));
            return isset($payload['userId']) ? (string) $payload['userId'] : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
