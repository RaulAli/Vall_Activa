<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Route;

use App\Application\Route\Command\CreateRouteFromSourceCommand;
use App\Application\Route\Handler\CreateRouteFromSourceHandler;
use App\Application\Shared\Security\CurrentUserProviderInterface;
use App\Domain\Identity\ValueObject\UserId;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CreateRouteFromSourceController extends AbstractController
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    #[Route('/api/routes', name: 'create_route_from_source', methods: ['POST'])]
    public function __invoke(
        Request $request,
        CurrentUserProviderInterface $currentUser,
        CreateRouteFromSourceHandler $handler
    ): JsonResponse {
        // Extract userId from Bearer JWT
        $userIdStr = $this->extractUserId($request);
        if ($userIdStr === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        try {
            $userId = UserId::fromString($userIdStr);
        } catch (\InvalidArgumentException) {
            return $this->json(['error' => 'bad_request', 'message' => 'Invalid user id'], 400);
        }

        // Read multipart fields
        $title = trim((string) $request->request->get('title', ''));
        $slug = trim((string) $request->request->get('slug', ''));
        if ($title === '' || $slug === '') {
            return $this->json(['error' => 'bad_request', 'message' => 'title and slug are required'], 400);
        }

        $description = $request->request->get('description') ?: null;
        $sportCode = (string) $request->request->get('sportCode', 'hike');
        $visibility = (string) $request->request->get('visibility', 'PRIVATE');
        $status = (string) $request->request->get('status', 'PUBLISHED');
        $sourceFormat = strtoupper((string) $request->request->get('sourceFormat', 'GPX'));

        if (!in_array($visibility, ['PUBLIC', 'UNLISTED', 'PRIVATE'], true)) {
            $visibility = 'PUBLIC';
        }
        if (!in_array($status, ['DRAFT', 'PUBLISHED', 'ARCHIVED'], true)) {
            $status = 'PUBLISHED';
        }

        $difficulty = strtoupper((string) $request->request->get('difficulty', ''));
        if (!in_array($difficulty, ['EASY', 'MODERATE', 'HARD', 'EXPERT'], true)) {
            $difficulty = null;
        }

        $routeType = strtoupper((string) $request->request->get('routeType', ''));
        if (!in_array($routeType, ['CIRCULAR', 'LINEAR', 'ROUND_TRIP'], true)) {
            $routeType = null;
        }

        // Handle uploaded file
        $file = $request->files->get('file');
        if ($file === null) {
            return $this->json(['error' => 'bad_request', 'message' => 'GPX file is required'], 400);
        }

        // Capture metadata before move() invalidates the object
        $originalFilename = $file->getClientOriginalName() ?: 'route.gpx';
        $mimeType = $file->getMimeType() ?: 'application/gpx+xml';

        // Move to a temp location so the parser can read it by absolute path
        $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('route_', true) . '.gpx';
        $file->move(dirname($tmpPath), basename($tmpPath));

        try {
            $actor = $currentUser->actorFromUserId($userId);

            $id = $handler(new CreateRouteFromSourceCommand(
                actor: $actor,
                title: $title,
                slug: $slug,
                description: $description,
                sportCode: $sportCode,
                visibility: $visibility,
                status: $status,
                sourceFormat: $sourceFormat,
                sourcePath: $tmpPath,
                originalFilename: $originalFilename,
                mimeType: $mimeType,
                fileSize: (int) filesize($tmpPath),
                sha256: hash_file('sha256', $tmpPath) ?: null,
                difficulty: $difficulty,
                routeType: $routeType,
            ));
        } catch (\InvalidArgumentException $e) {
            @unlink($tmpPath);
            return $this->json(['error' => 'bad_request', 'message' => $e->getMessage()], 400);
        } catch (\DomainException $e) {
            @unlink($tmpPath);
            return $this->json(['error' => $e->getMessage()], 422);
        } finally {
            // Clean up temp file (already read by parser at this point)
            if (file_exists($tmpPath)) {
                @unlink($tmpPath);
            }
        }

        return $this->json(['id' => $id], 201);
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
