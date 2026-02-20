<?php
declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Auth\Port\JwtTokenGeneratorInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\UserOrm;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class LexikJwtTokenGenerator implements JwtTokenGeneratorInterface
{
    public function __construct(private readonly JWTTokenManagerInterface $jwtManager) {}

    public function generate(string $userId, string $email, array $extraClaims = []): string
    {
        // Build a temporary UserOrm so LexikJWT can extract the identifier
        $user        = new UserOrm();
        $user->id    = $userId;
        $user->email = $email;

        // Merge userId as a custom claim so we can read it from the token payload
        $claims = array_merge(['userId' => $userId], $extraClaims);

        return $this->jwtManager->createFromPayload($user, $claims);
    }
}
