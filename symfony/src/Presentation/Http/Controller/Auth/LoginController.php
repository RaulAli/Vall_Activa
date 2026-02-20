<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Auth;

use App\Application\Auth\Command\LoginCommand;
use App\Application\Auth\Handler\LoginHandler;
use App\Presentation\Http\Request\Auth\LoginRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class LoginController extends AbstractController
{
    private const COOKIE_NAME = 'refresh_token';

    public function __construct(private readonly bool $cookieSecure)
    {
    }

    #[Route('/api/auth/login', name: 'auth_login', methods: ['POST'])]
    public function __invoke(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        LoginHandler $handler,
    ): JsonResponse {
        /** @var LoginRequest $dto */
        $dto = $serializer->deserialize($request->getContent(), LoginRequest::class, 'json');

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['error' => 'validation_failed', 'message' => (string) $errors], 422);
        }

        try {
            $tokens = $handler(new LoginCommand(
                email: $dto->email,
                plainPassword: $dto->password,
                deviceId: $dto->deviceId,
            ));
        } catch (\DomainException $e) {
            return $this->json(['error' => 'invalid_credentials'], 401);
        }

        $response = $this->json([
            'accessToken' => $tokens->accessToken,
            'userId' => $tokens->userId,
            'email' => $tokens->email,
        ]);

        $response->headers->setCookie(
            new Cookie(
                name: self::COOKIE_NAME,
                value: $tokens->rawRefreshToken,
                expire: time() + $tokens->refreshTtl,
                path: '/api/auth',
                domain: null,
                secure: $this->cookieSecure,
                httpOnly: true,
                raw: false,
                sameSite: Cookie::SAMESITE_STRICT,
            )
        );

        return $response;
    }
}
