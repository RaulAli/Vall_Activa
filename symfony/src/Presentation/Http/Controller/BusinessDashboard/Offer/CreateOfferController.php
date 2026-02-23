<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\BusinessDashboard\Offer;

use App\Application\Offer\Command\CreateOfferCommand;
use App\Application\Offer\Handler\CreateOfferHandler;
use App\Application\Shared\Security\CurrentUserProviderInterface;
use App\Domain\Identity\ValueObject\UserId;
use App\Presentation\Http\Request\Offer\CreateOfferRequest;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CreateOfferController extends AbstractController
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    #[Route('/api/business/me/offers', name: 'business_create_offer', methods: ['POST'])]
    public function __invoke(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        CurrentUserProviderInterface $currentUser,
        CreateOfferHandler $handler
    ): JsonResponse {
        $userId = $this->extractUserId($request);
        if ($userId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        try {
            $userIdObj = UserId::fromString($userId);
        } catch (\InvalidArgumentException) {
            return $this->json(['error' => 'bad_request', 'message' => 'Invalid user id'], 400);
        }

        /** @var CreateOfferRequest $dto */
        $dto = $serializer->deserialize($request->getContent(), CreateOfferRequest::class, 'json');

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['error' => 'bad_request', 'message' => (string) $errors], 400);
        }

        $actor = $currentUser->actorFromUserId($userIdObj);

        $image = $dto->image;
        if ($image !== null && trim($image) === '') {
            $image = null;
        }

        try {
            $id = $handler(new CreateOfferCommand(
                actor: $actor,
                title: $dto->title,
                slug: $dto->slug,
                description: $dto->description,
                price: $dto->price,
                currency: $dto->currency,
                isActive: $dto->isActive,
                quantity: $dto->quantity,
                pointsCost: $dto->pointsCost,
                image: $image,
                discountType: $dto->discountType,
                status: $dto->status
            ));
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => 'bad_request', 'message' => $e->getMessage()], 400);
        } catch (\DomainException $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        }

        return $this->json(['id' => $id], 201);
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
