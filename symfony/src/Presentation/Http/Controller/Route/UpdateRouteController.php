<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Route;

use App\Infrastructure\Persistence\Doctrine\Entity\Route\RouteOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Sport\SportOrm;
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
    private const DIFFICULTY_VALUES = ['EASY', 'MODERATE', 'HARD', 'EXPERT'];
    private const ROUTE_TYPE_VALUES = ['CIRCULAR', 'LINEAR', 'ROUND_TRIP'];

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

        // title — if changed, also auto-regenerate slug
        if (isset($body['title']) && is_string($body['title'])) {
            $title = trim($body['title']);
            if ($title !== '') {
                $route->title = mb_substr($title, 0, 255);

                // Auto-generate slug from new title with uniqueness check
                $newSlug = mb_strtolower(
                    trim((string) preg_replace('/[^a-z0-9]+/i', '-', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $title) ?: $title)),
                    'UTF-8'
                );
                $newSlug = trim(mb_substr($newSlug, 0, 60), '-');
                if ($newSlug !== '' && $newSlug !== $route->slug) {
                    $conflict = $em->createQueryBuilder()
                        ->select('COUNT(r.id)')
                        ->from(RouteOrm::class, 'r')
                        ->where('r.createdByUserId = :uid AND r.slug = :slug AND r.id != :rid')
                        ->setParameter('uid', $userId)
                        ->setParameter('slug', $newSlug)
                        ->setParameter('rid', $id)
                        ->getQuery()
                        ->getSingleScalarResult();
                    if ((int) $conflict === 0) {
                        $route->slug = $newSlug;
                    }
                }
            }
        }

        // description
        if (array_key_exists('description', $body)) {
            $route->description = is_string($body['description']) && trim($body['description']) !== ''
                ? trim($body['description'])
                : null;
        }

        // sportCode — look up the sport and update sportId
        if (isset($body['sportCode']) && is_string($body['sportCode'])) {
            $sport = $em->createQueryBuilder()
                ->select('s')
                ->from(SportOrm::class, 's')
                ->where('s.code = :code')
                ->setParameter('code', mb_strtoupper(trim($body['sportCode'])))
                ->getQuery()
                ->getOneOrNullResult();
            if ($sport instanceof SportOrm) {
                $route->sportId = $sport->id;
            }
        }

        // visibility
        if (isset($body['visibility']) && in_array($body['visibility'], self::VISIBILITY_VALUES, true)) {
            $route->visibility = (string) $body['visibility'];
        }

        // status
        if (isset($body['status']) && in_array($body['status'], self::STATUS_VALUES, true)) {
            $route->status = (string) $body['status'];
        }

        // difficulty
        if (array_key_exists('difficulty', $body)) {
            $route->difficulty = (isset($body['difficulty']) && in_array($body['difficulty'], self::DIFFICULTY_VALUES, true))
                ? (string) $body['difficulty']
                : null;
        }

        // routeType
        if (array_key_exists('routeType', $body)) {
            $route->routeType = (isset($body['routeType']) && in_array($body['routeType'], self::ROUTE_TYPE_VALUES, true))
                ? (string) $body['routeType']
                : null;
        }

        $route->updatedAt = new \DateTimeImmutable();
        $em->flush();

        return $this->json([
            'id' => $route->id,
            'title' => $route->title,
            'slug' => $route->slug,
            'description' => $route->description,
            'visibility' => $route->visibility,
            'status' => $route->status,
            'sportId' => $route->sportId,
            'difficulty' => $route->difficulty,
            'routeType' => $route->routeType,
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
