<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Incident;

use App\Application\Incident\Command\CreateIncidentCommand;
use App\Application\Incident\Handler\CreateIncidentHandler;
use App\Application\Shared\Security\CurrentUserProviderInterface;
use App\Domain\Identity\ValueObject\UserId;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class CreateIncidentController extends AbstractController
{
    public function __construct(private readonly JWTTokenManagerInterface $jwtManager)
    {
    }

    #[Route('/api/incidents', name: 'create_incident', methods: ['POST'])]
    public function __invoke(
        Request $request,
        CurrentUserProviderInterface $currentUser,
        CreateIncidentHandler $handler,
    ): JsonResponse {
        $userIdStr = $this->extractUserId($request);
        if ($userIdStr === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['error' => 'bad_request', 'message' => 'Invalid JSON body.'], 400);
        }

        try {
            $userId = UserId::fromString($userIdStr);
            $actor = $currentUser->actorFromUserId($userId);

            $id = $handler(new CreateIncidentCommand(
                actor: $actor,
                category: (string) ($payload['category'] ?? ''),
                subject: (string) ($payload['subject'] ?? ''),
                message: (string) ($payload['message'] ?? ''),
            ));
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => 'bad_request', 'message' => $e->getMessage()], 400);
        } catch (HttpExceptionInterface $e) {
            return $this->json(['error' => 'forbidden'], $e->getStatusCode());
        } catch (\Throwable $e) {
            return $this->json(['error' => 'internal_error'], 500);
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
