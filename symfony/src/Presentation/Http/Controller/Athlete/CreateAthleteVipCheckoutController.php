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

final class CreateAthleteVipCheckoutController extends AbstractController
{
    public function __construct(private readonly JWTTokenManagerInterface $jwtManager)
    {
    }

    #[Route('/api/athlete/me/vip/payment/checkout', name: 'athlete_vip_checkout_create', methods: ['POST'])]
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

        $secretKey = (string) (getenv('STRIPE_SECRET_KEY') ?: '');
        if ($secretKey === '') {
            return $this->json(['error' => 'stripe_not_configured'], 503);
        }

        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['error' => 'invalid_json'], 400);
        }

        $plan = isset($payload['plan']) && is_string($payload['plan']) ? strtoupper(trim($payload['plan'])) : '';
        if (!in_array($plan, ['MONTHLY', 'YEARLY'], true)) {
            return $this->json(['error' => 'invalid_vip_plan'], 422);
        }

        $returnOrigin = isset($payload['returnOrigin']) && is_string($payload['returnOrigin'])
            ? trim($payload['returnOrigin'])
            : '';

        if ($returnOrigin === '' || !preg_match('#^https?://#i', $returnOrigin)) {
            return $this->json(['error' => 'invalid_return_origin'], 422);
        }

        $isMonthlyUpgradeToYearly = $plan === 'YEARLY'
            && $user->vipActive
            && $user->vipPlan === 'MONTHLY'
            && $user->vipExpiresAt instanceof \DateTimeImmutable
            && $user->vipExpiresAt > new \DateTimeImmutable();

        $amountCents = $this->planAmountCents($plan, $isMonthlyUpgradeToYearly);
        $currency = strtolower($this->vipCurrency());
        $productName = $plan === 'YEARLY'
            ? ($isMonthlyUpgradeToYearly ? 'Upgrade VIP Anual desde Mensual' : 'VAMO VIP Anual')
            : 'VAMO VIP Mensual';

        $stripe = new StripeClient($secretKey);

        try {
            $session = $stripe->checkout->sessions->create([
                'mode' => 'payment',
                'line_items' => [
                    [
                        'quantity' => 1,
                        'price_data' => [
                            'currency' => $currency,
                            'unit_amount' => $amountCents,
                            'product_data' => [
                                'name' => $productName,
                            ],
                        ],
                    ]
                ],
                'metadata' => [
                    'vipPlan' => $plan,
                    'athleteUserId' => $athleteUserId,
                    'vipUpgradeFromMonthly' => $isMonthlyUpgradeToYearly ? '1' : '0',
                    'vipAmountCents' => (string) $amountCents,
                ],
                'success_url' => rtrim($returnOrigin, '/') . '/plans?checkout=success&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => rtrim($returnOrigin, '/') . '/plans?checkout=cancel',
            ]);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'stripe_checkout_failed', 'message' => $e->getMessage()], 502);
        }

        return $this->json([
            'checkoutUrl' => (string) $session->url,
            'sessionId' => (string) $session->id,
            'plan' => $plan,
            'amountCents' => $amountCents,
            'currency' => strtoupper($currency),
            'isUpgradeFromMonthly' => $isMonthlyUpgradeToYearly,
        ]);
    }

    private function planAmountCents(string $plan, bool $isMonthlyUpgradeToYearly = false): int
    {
        if ($plan === 'YEARLY') {
            if ($isMonthlyUpgradeToYearly) {
                return 9000;
            }

            $configured = getenv('STRIPE_VIP_YEARLY_AMOUNT_CENTS');
            if (is_string($configured) && ctype_digit($configured) && (int) $configured > 0) {
                return (int) $configured;
            }
            return 10000;
        }

        $configured = getenv('STRIPE_VIP_MONTHLY_AMOUNT_CENTS');
        if (is_string($configured) && ctype_digit($configured) && (int) $configured > 0) {
            return (int) $configured;
        }

        return 1000;
    }

    private function vipCurrency(): string
    {
        $configured = getenv('STRIPE_VIP_CURRENCY');
        if (!is_string($configured) || trim($configured) === '') {
            return 'EUR';
        }

        return strtoupper(trim($configured));
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
