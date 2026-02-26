<?php
declare(strict_types=1);

namespace App\Application\Auth\Handler;

use App\Application\Auth\AuthConfig;
use App\Application\Auth\Command\RefreshTokenCommand;
use App\Application\Auth\DTO\AuthTokensDto;
use App\Application\Auth\Port\JwtTokenGeneratorInterface;
use App\Application\Auth\Port\RefreshSessionRepositoryInterface;
use App\Application\Auth\Port\TokenBlacklistRepositoryInterface;
use App\Application\Auth\Port\UserAuthRepositoryInterface;

final class RefreshTokenHandler
{

    public function __construct(
        private readonly UserAuthRepositoryInterface $users,
        private readonly JwtTokenGeneratorInterface $jwt,
        private readonly RefreshSessionRepositoryInterface $sessions,
        private readonly TokenBlacklistRepositoryInterface $blacklist,
    ) {
    }

    /** @throws \DomainException */
    public function __invoke(RefreshTokenCommand $cmd): AuthTokensDto
    {
        $tokenHash = hash('sha256', $cmd->rawRefreshToken);

        // 1. Blacklist check
        if ($this->blacklist->isBlacklisted($tokenHash)) {
            throw new \DomainException('token_blacklisted');
        }

        // 2. Reuse detection — kill the whole family if replayed rotated token
        $revokedSession = $this->sessions->findRevokedByTokenHash($tokenHash);
        if ($revokedSession !== null) {
            $this->sessions->revokeByFamily($revokedSession['familyId']);
            throw new \DomainException('token_reuse_detected');
        }

        // 3. Session lookup
        $session = $this->sessions->findActiveByTokenHash($tokenHash);
        if ($session === null) {
            throw new \DomainException('session_not_found');
        }

        // 4. Expiry check
        if ($session['expiresAt'] < new \DateTimeImmutable()) {
            $this->sessions->revoke($session['id']);
            throw new \DomainException('refresh_token_expired');
        }

        // 5. User still active
        $userId = $session['userId'];
        $userById = $this->users->findById($userId);
        if ($userById === null || !$userById->isActive) {
            $this->sessions->revoke($session['id']);
            throw new \DomainException('user_inactive');
        }

        // 6. Rotate token
        $this->blacklist->add($tokenHash, $userId, $session['expiresAt']);

        $newRawToken = bin2hex(random_bytes(64));
        $newTokenHash = hash('sha256', $newRawToken);
        $now = new \DateTimeImmutable();
        $newExpiresAt = $now->modify(sprintf('+%d seconds', AuthConfig::REFRESH_TTL_SECONDS));

        $this->sessions->rotateToken($session['id'], $newTokenHash, $newExpiresAt);

        $accessToken = $this->jwt->generate($userById->id, $userById->email, [
            'sessionId' => $session['id'],
            'sessionVersion' => $session['sessionVersion'],
        ]);

        return new AuthTokensDto(
            accessToken: $accessToken,
            rawRefreshToken: $newRawToken,
            refreshTtl: AuthConfig::REFRESH_TTL_SECONDS,
            userId: $userById->id,
            email: $userById->email,
        );
    }
}
