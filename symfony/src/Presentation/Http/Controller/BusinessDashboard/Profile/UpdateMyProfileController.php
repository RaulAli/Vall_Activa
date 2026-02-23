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

final class UpdateMyProfileController extends AbstractController
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    #[Route('/api/business/me/profile', name: 'business_update_profile', methods: ['PATCH'])]
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

        $body = json_decode($request->getContent(), true) ?? [];

        if (isset($body['name']) && is_string($body['name']) && trim($body['name']) !== '') {
            $profile->name = trim($body['name']);
        }

        if (isset($body['profileIcon']) && is_string($body['profileIcon'])) {
            $profile->profileIcon = trim($body['profileIcon']) ?: null;
        }

        if (array_key_exists('lat', $body)) {
            $profile->lat = is_numeric($body['lat']) ? (float) $body['lat'] : null;
        }

        if (array_key_exists('lng', $body)) {
            $profile->lng = is_numeric($body['lng']) ? (float) $body['lng'] : null;
        }

        $profile->updatedAt = new \DateTimeImmutable();
        $em->flush();

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
