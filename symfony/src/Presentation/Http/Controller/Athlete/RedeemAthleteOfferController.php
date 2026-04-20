<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Athlete;

use App\Domain\Shared\ValueObject\Uuid;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\UserOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Offer\OfferOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Offer\OfferRedemptionOrm;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class RedeemAthleteOfferController extends AbstractController
{
    public function __construct(private readonly JWTTokenManagerInterface $jwtManager)
    {
    }

    #[Route('/api/athlete/me/offers/{id}/redeem', name: 'athlete_offer_redeem', methods: ['POST'])]
    public function __invoke(string $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $athleteUserId = $this->extractUserId($request);
        if ($athleteUserId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        $athlete = $em->find(UserOrm::class, $athleteUserId);
        if (!$athlete instanceof UserOrm || !$athlete->isActive || $athlete->role !== 'ROLE_ATHLETE') {
            return $this->json(['error' => 'forbidden_role'], 403);
        }

        $offer = $em->find(OfferOrm::class, $id);
        if (!$offer instanceof OfferOrm || $offer->adminDisabled || !$offer->isActive || $offer->status !== 'PUBLISHED') {
            return $this->json(['error' => 'offer_not_found'], 404);
        }

        if ($offer->pointsCost <= 0) {
            return $this->json(['error' => 'offer_not_redeemable_by_points'], 409);
        }

        if ($athlete->pointsBalance < $offer->pointsCost) {
            return $this->json([
                'error' => 'insufficient_points',
                'required' => $offer->pointsCost,
                'balance' => $athlete->pointsBalance,
            ], 409);
        }

        $athlete->pointsBalance -= $offer->pointsCost;
        if ($offer->quantity > 0) {
            $offer->quantity -= 1;
            if ($offer->quantity === 0) {
                $offer->isActive = false;
            }
        }
        $offer->updatedAt = new \DateTimeImmutable();

        $redemption = new OfferRedemptionOrm();
        $redemption->id = Uuid::v4()->value();
        $redemption->offerId = $offer->id;
        $redemption->athleteUserId = $athlete->id;
        $redemption->pointsSpent = $offer->pointsCost;
        $redemption->createdAt = new \DateTimeImmutable();

        $em->persist($redemption);
        $em->flush();

        return $this->json([
            'redemptionId' => $redemption->id,
            'offerId' => $offer->id,
            'pointsSpent' => $redemption->pointsSpent,
            'balance' => $athlete->pointsBalance,
            'quantityLeft' => $offer->quantity,
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
