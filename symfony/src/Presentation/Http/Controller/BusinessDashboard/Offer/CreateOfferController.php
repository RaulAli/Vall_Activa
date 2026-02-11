<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\BusinessDashboard\Offer;

use App\Application\Offer\Command\CreateOfferCommand;
use App\Application\Offer\Handler\CreateOfferHandler;
use App\Application\Shared\Security\CurrentUserProviderInterface;
use App\Domain\Identity\ValueObject\UserId;
use App\Presentation\Http\Request\Offer\CreateOfferRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CreateOfferController extends AbstractController
{
    #[Route('/api/business/me/offers', name: 'business_create_offer', methods: ['POST'])]
    public function __invoke(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        CurrentUserProviderInterface $currentUser,
        CreateOfferHandler $handler
    ): JsonResponse {
        /** @var CreateOfferRequest $dto */
        $dto = $serializer->deserialize($request->getContent(), CreateOfferRequest::class, 'json');

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['error' => 'bad_request', 'message' => (string) $errors], 400);
        }

        $userIdHeader = (string) $request->headers->get('X-User-Id', '');
        if ($userIdHeader === '') {
            return $this->json(['error' => 'bad_request', 'message' => 'Missing X-User-Id header'], 400);
        }

        try {
            $userId = UserId::fromString($userIdHeader);
        } catch (\InvalidArgumentException) {
            return $this->json(['error' => 'bad_request', 'message' => 'Invalid X-User-Id UUID'], 400);
        }

        // Roles reales desde DB
        $actor = $currentUser->actorFromUserId($userId);

        // Normaliza image si llega vacío ""
        $image = $dto->image;
        if ($image !== null && trim($image) === '') {
            $image = null;
        }

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

        return $this->json(['id' => $id], 201);
    }
}
