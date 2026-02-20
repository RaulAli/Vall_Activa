<?php
declare(strict_types=1);

namespace App\Application\Auth\Handler;

use App\Application\Auth\Command\LoginCommand;
use App\Application\Auth\DTO\AuthTokensDto;
use App\Application\Auth\Port\JwtTokenGeneratorInterface;
use App\Application\Auth\Port\PasswordHasherInterface;
use App\Application\Auth\Port\RefreshSessionRepositoryInterface;
use App\Application\Auth\Port\TokenBlacklistRepositoryInterface;
use App\Application\Auth\Port\UserAuthRepositoryInterface;
use App\Domain\Shared\ValueObject\Uuid;

final class LoginHandler
{
    /** Refresh token lives 30 days */
    private const REFRESH_TTL_SECONDS = 30 * 24 * 60 * 60;

    public function __construct(
        private readonly UserAuthRepositoryInterface $users,
        private readonly PasswordHasherInterface $hasher,
        private readonly JwtTokenGeneratorInterface $jwt,
        private readonly RefreshSessionRepositoryInterface $sessions,
        private readonly TokenBlacklistRepositoryInterface $blacklist,
    ) {
    }

    /** @throws \DomainException */
    public function __invoke(LoginCommand $cmd): AuthTokensDto
    {
        $email = mb_strtolower(trim($cmd->email));
        $user = $this->users->findByEmail($email);

        if ($user === null || !$user->isActive) {
            throw new \DomainException('invalid_credentials');
        }

        if (!$this->hasher->verify($user->hashedPassword, $cmd->plainPassword)) {
            throw new \DomainException('invalid_credentials');
        }

        // Generate a new raw refresh token
        $rawToken = bin2hex(random_bytes(64)); // 128-char hex
        $tokenHash = hash('sha256', $rawToken);

        $deviceId = $cmd->deviceId ?? Uuid::v4()->value();
        $familyId = Uuid::v4()->value();
        $sessionId = Uuid::v4()->value();
        $now = new \DateTimeImmutable();
        $expiresAt = $now->modify(sprintf('+%d seconds', self::REFRESH_TTL_SECONDS));

        $sessionVersion = 1;

        $this->sessions->create([
            'id' => $sessionId,
            'userId' => $user->id,
            'deviceId' => $deviceId,
            'familyId' => $familyId,
            'tokenHash' => $tokenHash,
            'sessionVersion' => $sessionVersion,
            'expiresAt' => $expiresAt,
        ]);

        $accessToken = $this->jwt->generate($user->id, $user->email, [
            'sessionId' => $sessionId,
            'sessionVersion' => $sessionVersion,
        ]);

        return new AuthTokensDto(
            accessToken: $accessToken,
            rawRefreshToken: $rawToken,
            refreshTtl: self::REFRESH_TTL_SECONDS,
            userId: $user->id,
            email: $user->email,
        );
    }
}
