<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Auth;

use App\Application\Auth\Command\RegisterCommand;
use App\Application\Auth\Handler\RegisterHandler;
use App\Presentation\Http\Request\Auth\RegisterRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RegisterController extends AbstractController
{
    #[Route('/api/auth/register', name: 'auth_register', methods: ['POST'])]
    public function __invoke(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        RegisterHandler $handler,
    ): JsonResponse {
        /** @var RegisterRequest $dto */
        $dto = $serializer->deserialize($request->getContent(), RegisterRequest::class, 'json');

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            $fieldErrors = [];
            foreach ($errors as $violation) {
                $field = $violation->getPropertyPath();
                $fieldErrors[$field][] = $violation->getMessage();
            }
            return $this->json(['error' => 'validation_failed', 'fields' => $fieldErrors], 422);
        }

        try {
            $id = $handler(new RegisterCommand(
                email: $dto->email,
                plainPassword: $dto->password,
                role: $dto->role,
                name: $dto->name,
                slug: $dto->slug,
            ));
        } catch (\DomainException $e) {
            return match ($e->getMessage()) {
                'email_already_taken' => $this->json(['error' => 'email_already_taken'], 409),
                'slug_already_taken' => $this->json(['error' => 'slug_already_taken'], 409),
                default => $this->json(['error' => $e->getMessage()], 400),
            };
        }

        return $this->json(['id' => $id], 201);
    }
}
