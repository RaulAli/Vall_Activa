<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Athlete;

use App\Infrastructure\Persistence\Doctrine\Entity\Identity\UserOrm;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ConfirmAthleteVipPaymentController extends AbstractController
{
    public function __construct(private readonly JWTTokenManagerInterface $jwtManager)
    {
    }

    #[Route('/api/athlete/me/vip/payment/confirm', name: 'athlete_vip_payment_confirm', methods: ['POST'])]
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

        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['error' => 'invalid_json'], 400);
        }

        $sessionId = isset($payload['sessionId']) && is_string($payload['sessionId']) ? trim($payload['sessionId']) : '';
        if ($sessionId === '') {
            return $this->json(['error' => 'missing_session_id'], 422);
        }

        $secretKey = (string) (getenv('STRIPE_SECRET_KEY') ?: '');
        if ($secretKey === '') {
            return $this->json(['error' => 'stripe_not_configured'], 503);
        }

        $stripe = new StripeClient($secretKey);

        try {
            $session = $stripe->checkout->sessions->retrieve($sessionId);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'stripe_session_fetch_failed', 'message' => $e->getMessage()], 502);
        }

        $isPaid = (($session->payment_status ?? null) === 'paid');
        if (!$isPaid) {
            return $this->json(['error' => 'payment_not_completed'], 409);
        }

        $metaAthleteId = isset($session->metadata->athleteUserId) ? (string) $session->metadata->athleteUserId : '';
        if ($metaAthleteId === '' || $metaAthleteId !== $athleteUserId) {
            return $this->json(['error' => 'forbidden_payment_session'], 403);
        }

        $plan = isset($session->metadata->vipPlan) ? strtoupper((string) $session->metadata->vipPlan) : '';
        if (!in_array($plan, ['MONTHLY', 'YEARLY'], true)) {
            return $this->json(['error' => 'invalid_vip_plan'], 422);
        }

        $now = new \DateTimeImmutable();

        $renewBase = $now;
        if ($user->vipExpiresAt instanceof \DateTimeImmutable && $user->vipExpiresAt > $now) {
            $renewBase = $user->vipExpiresAt;
        }

        $newExpiry = $plan === 'YEARLY'
            ? $renewBase->modify('+1 year')
            : $renewBase->modify('+1 month');

        $user->vipActive = true;
        $user->vipPlan = $plan;
        $user->vipStartedAt = $now;
        $user->vipExpiresAt = $newExpiry;
        $user->vipCancelAtPeriodEnd = false;
        $user->updatedAt = $now;
        $em->flush();

        return $this->json([
            'isVip' => $user->vipActive,
            'vipPlan' => $user->vipPlan,
            'vipStartedAt' => $user->vipStartedAt?->format(DATE_ATOM),
            'vipExpiresAt' => $user->vipExpiresAt?->format(DATE_ATOM),
            'vipCancelAtPeriodEnd' => $user->vipCancelAtPeriodEnd,
            'paidAt' => $now->format(DATE_ATOM),
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
