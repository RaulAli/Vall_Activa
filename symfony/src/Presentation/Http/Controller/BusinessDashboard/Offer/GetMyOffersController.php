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

final class GetMyOffersController extends AbstractController
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    #[Route('/api/business/me/offers', name: 'business_list_offers', methods: ['GET'])]
    public function __invoke(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $userId = $this->extractUserId($request);
        if ($userId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        /** @var OfferOrm[] $offers */
        $offers = $em->getRepository(OfferOrm::class)->findBy(
            ['businessId' => $userId],
            ['createdAt' => 'DESC']
        );

        $data = array_map(static function (OfferOrm $o): array {
            return [
                'id' => $o->id,
                'title' => $o->title,
                'slug' => $o->slug,
                'description' => $o->description,
                'price' => $o->price,
                'currency' => $o->currency,
                'image' => $o->image,
                'discountType' => $o->discountType,
                'status' => $o->status,
                'quantity' => $o->quantity,
                'pointsCost' => $o->pointsCost,
                'isActive' => $o->isActive,
                'createdAt' => $o->createdAt->format(\DateTimeInterface::ATOM),
            ];
        }, $offers);

        return $this->json($data);
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
