<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Athlete;

use App\Infrastructure\Persistence\Doctrine\Entity\Business\BusinessProfileOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\UserOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Offer\OfferOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Offer\OfferRedemptionOrm;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ListAthleteOfferRedemptionsController extends AbstractController
{
    public function __construct(private readonly JWTTokenManagerInterface $jwtManager)
    {
    }

    #[Route('/api/athlete/me/offers/redemptions', name: 'athlete_offer_redemptions_list', methods: ['GET'])]
    public function __invoke(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $athleteUserId = $this->extractUserId($request);
        if ($athleteUserId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        $athlete = $em->find(UserOrm::class, $athleteUserId);
        if (!$athlete instanceof UserOrm || !$athlete->isActive || $athlete->role !== 'ROLE_ATHLETE') {
            return $this->json(['error' => 'forbidden_role'], 403);
        }

        $rows = $em->createQueryBuilder()
            ->select('r.id AS redemptionId, r.pointsSpent, r.createdAt, o.id AS offerId, o.title AS offerTitle, o.slug AS offerSlug, o.image AS offerImage, b.name AS businessName, b.slug AS businessSlug')
            ->from(OfferRedemptionOrm::class, 'r')
            ->innerJoin(OfferOrm::class, 'o', 'WITH', 'o.id = r.offerId')
            ->leftJoin(BusinessProfileOrm::class, 'b', 'WITH', 'b.userId = o.businessId')
            ->andWhere('r.athleteUserId = :athleteUserId')
            ->setParameter('athleteUserId', $athleteUserId)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getArrayResult();

        $payload = array_map(static function (array $row): array {
            $redemptionId = (string) ($row['redemptionId'] ?? '');

            return [
                'redemptionId' => $redemptionId,
                'pointsSpent' => (int) ($row['pointsSpent'] ?? 0),
                'redeemedAt' => $row['createdAt'] instanceof \DateTimeInterface
                    ? $row['createdAt']->format(DATE_ATOM)
                    : null,
                'qrPayload' => 'VAMO:REDEMPTION:' . $redemptionId,
                'offer' => [
                    'id' => (string) ($row['offerId'] ?? ''),
                    'title' => (string) ($row['offerTitle'] ?? ''),
                    'slug' => (string) ($row['offerSlug'] ?? ''),
                    'image' => isset($row['offerImage']) && is_string($row['offerImage']) ? $row['offerImage'] : null,
                    'businessName' => isset($row['businessName']) && is_string($row['businessName']) ? $row['businessName'] : null,
                    'businessSlug' => isset($row['businessSlug']) && is_string($row['businessSlug']) ? $row['businessSlug'] : null,
                ],
            ];
        }, $rows);

        return $this->json($payload);
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
