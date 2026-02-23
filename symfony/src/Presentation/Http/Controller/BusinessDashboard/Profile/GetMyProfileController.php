<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\BusinessDashboard\Profile;

use App\Infrastructure\Persistence\Doctrine\Entity\Business\BusinessProfileOrm;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class GetMyProfileController extends AbstractController
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    #[Route('/api/business/me/profile', name: 'business_get_profile', methods: ['GET'])]
    public function __invoke(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $userId = $this->extractUserId($request);
        if ($userId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        /** @var BusinessProfileOrm|null $profile */
        $profile = $em->find(BusinessProfileOrm::class, $userId);

        if ($profile === null) {
            return $this->json(['error' => 'profile_not_found'], 404);
        }

        return $this->json([
            'userId' => $profile->userId,
            'name' => $profile->name,
            'slug' => $profile->slug,
            'profileIcon' => $profile->profileIcon,
            'lat' => $profile->lat,
            'lng' => $profile->lng,
            'isActive' => $profile->isActive,
        ]);
    }

    private function extractUserId(Request $request): ?string
    {
        $authHeader = $request->headers->get('Authorization', '');
        if (!str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }
        $token = substr($authHeader, 7);
        try {
            $payload = $this->jwtManager->parse($token);
            return isset($payload['userId']) ? (string) $payload['userId'] : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
