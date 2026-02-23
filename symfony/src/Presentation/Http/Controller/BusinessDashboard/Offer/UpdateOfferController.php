<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\BusinessDashboard\Offer;

use App\Infrastructure\Persistence\Doctrine\Entity\Offer\OfferOrm;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class UpdateOfferController extends AbstractController
{
    private const STATUS_VALUES = ['DRAFT', 'PUBLISHED', 'ARCHIVED'];

    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    #[Route('/api/business/me/offers/{id}', name: 'business_update_offer', methods: ['PATCH'])]
    public function __invoke(string $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $userId = $this->extractUserId($request);
        if ($userId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        $offer = $em->getRepository(OfferOrm::class)->find($id);
        if ($offer === null) {
            return $this->json(['error' => 'not_found'], 404);
        }
        if ($offer->businessId !== $userId) {
            return $this->json(['error' => 'forbidden'], 403);
        }

        /** @var array<string, mixed> $body */
        $body = json_decode($request->getContent(), true) ?? [];

        if (isset($body['status']) && in_array($body['status'], self::STATUS_VALUES, true)) {
            $offer->status = (string) $body['status'];
        }
        if (isset($body['isActive'])) {
            $offer->isActive = (bool) $body['isActive'];
        }
        if (isset($body['title']) && is_string($body['title']) && trim($body['title']) !== '') {
            $offer->title = trim($body['title']);
        }
        if (array_key_exists('description', $body)) {
            $offer->description = is_string($body['description']) && trim($body['description']) !== ''
                ? trim($body['description'])
                : null;
        }
        if (isset($body['price']) && is_string($body['price']) && preg_match('/^\d+(\.\d{1,2})?$/', $body['price'])) {
            $offer->price = $body['price'];
        }
        if (isset($body['quantity']) && is_int($body['quantity']) && $body['quantity'] >= 0) {
            $offer->quantity = $body['quantity'];
        }
        if (isset($body['pointsCost']) && is_int($body['pointsCost']) && $body['pointsCost'] >= 0) {
            $offer->pointsCost = $body['pointsCost'];
        }
        if (isset($body['discountType']) && in_array($body['discountType'], ['NONE', 'AMOUNT', 'PERCENT'], true)) {
            $offer->discountType = (string) $body['discountType'];
        }
        if (isset($body['image'])) {
            $offer->image = is_string($body['image']) && trim($body['image']) !== '' ? trim($body['image']) : null;
        }

        $offer->updatedAt = new \DateTimeImmutable();
        $em->flush();

        return $this->json([
            'id' => $offer->id,
            'status' => $offer->status,
            'isActive' => $offer->isActive,
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
