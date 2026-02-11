<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Route;

use App\Application\Route\Command\CreateRouteFromSourceCommand;
use App\Application\Route\Handler\CreateRouteFromSourceHandler;
use App\Application\Shared\Security\CurrentUserProviderInterface;
use App\Domain\Identity\ValueObject\UserId;
use App\Presentation\Http\Request\Route\CreateRouteFromSourceRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CreateRouteFromSourceController extends AbstractController
{
    #[Route('/api/routes', name: 'create_route_from_source', methods: ['POST'])]
    public function __invoke(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        CurrentUserProviderInterface $currentUser,
        CreateRouteFromSourceHandler $handler
    ): JsonResponse {
        /** @var CreateRouteFromSourceRequest $dto */
        $dto = $serializer->deserialize($request->getContent(), CreateRouteFromSourceRequest::class, 'json');

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

        $actor = $currentUser->actorFromUserId($userId);

        $id = $handler(new CreateRouteFromSourceCommand(
            actor: $actor,
            title: $dto->title,
            slug: $dto->slug,
            description: $dto->description,
            sportCode: $dto->sportCode,
            visibility: $dto->visibility,
            status: $dto->status,
            sourceFormat: $dto->sourceFormat,
            sourcePath: $dto->sourcePath,
            originalFilename: $dto->originalFilename,
            mimeType: $dto->mimeType,
            fileSize: $dto->fileSize,
            sha256: $dto->sha256
        ));

        return $this->json(['id' => $id], 201);
    }
}
