<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Athlete;

use App\Infrastructure\Persistence\Doctrine\Entity\Identity\UserOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Route\GuideRouteBookingOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Route\RouteOrm;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CreateAthleteBookingCheckoutController extends AbstractController
{
    public function __construct(private readonly JWTTokenManagerInterface $jwtManager)
    {
    }

    #[Route('/api/athlete/me/bookings/{id}/payment/checkout', name: 'athlete_booking_checkout_create', methods: ['POST'])]
    public function __invoke(string $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $athleteUserId = $this->extractUserId($request);
        if ($athleteUserId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        $user = $em->find(UserOrm::class, $athleteUserId);
        if ($user === null || !$user->isActive || $user->role !== 'ROLE_ATHLETE') {
            return $this->json(['error' => 'forbidden_role'], 403);
        }

        $booking = $em->find(GuideRouteBookingOrm::class, $id);
        if ($booking === null || $booking->athleteUserId !== $athleteUserId) {
            return $this->json(['error' => 'booking_not_found'], 404);
        }

        if ($booking->status !== 'CONFIRMED') {
            return $this->json(['error' => 'booking_not_confirmed'], 409);
        }

        if ($booking->paymentStatus === 'PAID') {
            return $this->json(['error' => 'already_paid'], 409);
        }

        $secretKey = (string) (getenv('STRIPE_SECRET_KEY') ?: '');
        if ($secretKey === '') {
            return $this->json(['error' => 'stripe_not_configured'], 503);
        }

        $body = json_decode((string) $request->getContent(), true);
        $returnOrigin = is_array($body) && isset($body['returnOrigin']) && is_string($body['returnOrigin'])
            ? trim($body['returnOrigin'])
            : '';
        if ($returnOrigin === '' || !preg_match('#^https?://#i', $returnOrigin)) {
            return $this->json(['error' => 'invalid_return_origin'], 422);
        }

        $routeTitle = 'Reserva con guía';
        $route = $em->find(RouteOrm::class, $booking->routeId);
        if ($route !== null && trim((string) $route->title) !== '') {
            $routeTitle = 'Reserva ruta: ' . trim((string) $route->title);
        }

        $stripe = new StripeClient($secretKey);

        try {
            $session = $stripe->checkout->sessions->create([
                'mode' => 'payment',
                'line_items' => [
                    [
                        'quantity' => 1,
                        'price_data' => [
                            'currency' => strtolower($booking->paymentCurrency),
                            'unit_amount' => $booking->paymentAmountCents,
                            'product_data' => [
                                'name' => $routeTitle,
                            ],
                        ],
                    ]
                ],
                'metadata' => [
                    'bookingId' => $booking->id,
                    'athleteUserId' => $athleteUserId,
                ],
                'success_url' => rtrim($returnOrigin, '/') . '/me/bookings?checkout=success&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => rtrim($returnOrigin, '/') . '/me/bookings?checkout=cancel',
            ]);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'stripe_checkout_failed', 'message' => $e->getMessage()], 502);
        }

        $booking->stripeCheckoutSessionId = (string) $session->id;
        $booking->paymentStatus = 'PENDING';
        $booking->updatedAt = new \DateTimeImmutable();
        $em->flush();

        return $this->json([
            'checkoutUrl' => (string) $session->url,
            'sessionId' => (string) $session->id,
            'paymentStatus' => $booking->paymentStatus,
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
