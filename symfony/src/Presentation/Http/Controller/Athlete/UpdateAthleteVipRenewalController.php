<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Athlete;

use App\Infrastructure\Persistence\Doctrine\Entity\Identity\UserOrm;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class UpdateAthleteVipRenewalController extends AbstractController
{
    public function __construct(private readonly JWTTokenManagerInterface $jwtManager)
    {
    }

    #[Route('/api/athlete/me/vip/renewal', name: 'athlete_vip_renewal_update', methods: ['PATCH'])]
    public function __invoke(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $athleteUserId = $this->extractUserId($request);
        if ($athleteUserId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        $user = $em->find(UserOrm::class, $athleteUserId);
        if ($user === null || !$user->isActive || $user->role !== 'ROLE_ATHLETE') {
            return $this->json(['error' => 'forbidden_role'], 403);
        }

        if ($user->vipPlan === null || $user->vipExpiresAt === null) {
            return $this->json(['error' => 'no_vip_subscription'], 409);
        }

        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload) || !array_key_exists('cancelAtPeriodEnd', $payload)) {
            return $this->json(['error' => 'missing_cancel_at_period_end'], 422);
        }

        $cancelAtPeriodEnd = filter_var($payload['cancelAtPeriodEnd'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        if ($cancelAtPeriodEnd === null) {
            return $this->json(['error' => 'invalid_cancel_at_period_end'], 422);
        }

        $user->vipCancelAtPeriodEnd = $cancelAtPeriodEnd;
        $user->updatedAt = new \DateTimeImmutable();
        $em->flush();

        return $this->json([
            'isVip' => $user->vipActive,
            'vipPlan' => $user->vipPlan,
            'vipExpiresAt' => $user->vipExpiresAt?->format(DATE_ATOM),
            'vipCancelAtPeriodEnd' => $user->vipCancelAtPeriodEnd,
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
