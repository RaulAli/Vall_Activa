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

        // 1. Blacklist check (explicit logout)
        if ($this->blacklist->isBlacklisted($tokenHash)) {
            throw new \DomainException('token_blacklisted');
        }

        // 2. Idempotent rotation grace period (10 s):
        //    If the token was JUST rotated (e.g. F5 before Set-Cookie arrived), the client
        //    may retry with the old token. Return the already-rotated session instead of
        //    treating it as reuse/theft.
        $recentSession = $this->sessions->findRecentlyRotatedByPreviousHash($tokenHash, 10);
        if ($recentSession !== null) {
            // The rotation already happened — rebuild the response with the current token.
            $userById = $this->users->findById($recentSession['userId']);
            if ($userById === null || !$userById->isActive) {
                throw new \DomainException('user_inactive');
            }

            // Recover the raw token from the stored hash is not possible, so we rotate again
            // (issuing a fresh token) to give the client a usable cookie.
            $newRawToken = bin2hex(random_bytes(64));
            $newTokenHash = hash('sha256', $newRawToken);
            $now = new \DateTimeImmutable();
            $newExpiresAt = $now->modify(sprintf('+%d seconds', AuthConfig::REFRESH_TTL_SECONDS));

            $this->sessions->rotateToken($recentSession['id'], $newTokenHash, $newExpiresAt);

            $accessToken = $this->jwt->generate($userById->id, $userById->email, [
                'sessionId' => $recentSession['id'],
                'sessionVersion' => $recentSession['sessionVersion'],
            ]);

            return new AuthTokensDto(
                accessToken: $accessToken,
                rawRefreshToken: $newRawToken,
                refreshTtl: AuthConfig::REFRESH_TTL_SECONDS,
                userId: $userById->id,
                email: $userById->email,
            );
        }

        // 3. Reuse detection — token was rotated long ago or by another device: kill the family
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
