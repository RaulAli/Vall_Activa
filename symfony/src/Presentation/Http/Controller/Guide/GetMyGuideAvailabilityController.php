<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Guide;

use App\Application\GuideAvailability\Handler\GetMyGuideAvailabilityHandler;
use App\Application\GuideAvailability\Query\GetMyGuideAvailabilityQuery;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class GetMyGuideAvailabilityController extends AbstractController
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    #[Route('/api/guide/me/availability', name: 'guide_me_availability_get', methods: ['GET'])]
    public function __invoke(Request $request, GetMyGuideAvailabilityHandler $handler): JsonResponse
    {
        $userId = $this->extractUserId($request);
        if ($userId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        try {
            $availability = $handler(new GetMyGuideAvailabilityQuery(userId: $userId));
        } catch (\DomainException $e) {
            if ($e->getMessage() === 'user_not_found') {
                return $this->json(['error' => 'user_not_found'], 404);
            }

            return $this->json(['error' => $e->getMessage()], 403);
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
