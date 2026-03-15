<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Guide;

use App\Application\GuideAvailability\Command\UpsertMyGuideAvailabilityCommand;
use App\Application\GuideAvailability\Handler\UpsertMyGuideAvailabilityHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class UpsertMyGuideAvailabilityController extends AbstractController
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    #[Route('/api/guide/me/availability', name: 'guide_me_availability_put', methods: ['PUT'])]
    public function __invoke(Request $request, UpsertMyGuideAvailabilityHandler $handler): JsonResponse
    {
        $userId = $this->extractUserId($request);
        if ($userId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        $body = json_decode($request->getContent(), true);
        if (!is_array($body)) {
            return $this->json(['error' => 'invalid_payload'], 422);
        }

        $week = $body['week'] ?? null;
        if (!is_array($week)) {
            return $this->json(['error' => 'invalid_week_payload'], 422);
        }

        try {
            $availability = $handler(new UpsertMyGuideAvailabilityCommand(
                userId: $userId,
                timezone: isset($body['timezone']) && is_string($body['timezone']) ? $body['timezone'] : null,
                week: $week,
            ));
        } catch (\DomainException $e) {
            $error = $e->getMessage();
            if ($error === 'user_not_found') {
                return $this->json(['error' => $error], 404);
            }
            if ($error === 'forbidden_role') {
                return $this->json(['error' => $error], 403);
            }

            return $this->json(['error' => $error], 422);
        }

        return $this->json([
            'timezone' => $availability->timezone,
            'slotMinutes' => $availability->slotMinutes,
            'week' => $availability->week,
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
