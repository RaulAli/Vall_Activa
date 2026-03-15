<?php
declare(strict_types=1);

namespace App\Application\Auth\Port;

interface RefreshSessionRepositoryInterface
{
    /**
     * Persists a new session entry.
     * @param array{id: string, userId: string, deviceId: string, familyId: string, tokenHash: string, sessionVersion: int, expiresAt: \DateTimeImmutable} $data
     */
    public function create(array $data): void;

    /**
     * Finds an active (non-revoked, non-expired) session by token hash.
     *
     * @return array{id: string, userId: string, deviceId: string, familyId: string, sessionVersion: int, expiresAt: \DateTimeImmutable}|null
     */
    public function findActiveByTokenHash(string $hash): ?array;

    /**
     * Finds a REVOKED session by token hash (used to detect refresh token reuse).
     *
     * @return array{id: string, userId: string, familyId: string}|null
     */
    public function findRevokedByTokenHash(string $hash): ?array;

    /**
     * Updates the token hash (rotation) and bumps updated_at.
     */
    public function rotateToken(string $sessionId, string $newHash, \DateTimeImmutable $newExpiresAt): void;

    /**
     * Finds an active session whose PREVIOUS token hash matches, rotated within the grace period.
     * Used to make refresh idempotent: if the client retries with an already-rotated token
     * (e.g. F5 before Set-Cookie arrived), we return the already-rotated session.
     *
     * @return array{id: string, userId: string, deviceId: string, familyId: string, sessionVersion: int, expiresAt: \DateTimeImmutable, currentTokenHash: string}|null
     */
    public function findRecentlyRotatedByPreviousHash(string $previousHash, int $graceSeconds = 10): ?array;

    /**
     * Marks a session as revoked.
     */
    public function revoke(string $sessionId): void;

    /**
     * Revokes all sessions for a given user (e.g., on password change).
     */
    public function revokeAllForUser(string $userId): void;

    /**
     * Revokes all sessions in a given family (detect token reuse/theft).
     */
    public function revokeByFamily(string $familyId): void;

    /**
     * Finds a session by its ID (used by the JWT validation listener).
     *
     * @return array{id: string, userId: string, sessionVersion: int, revoked: bool}|null
     */
    public function findById(string $sessionId): ?array;

    /**
     * Increments session_version on all active sessions for a user and revokes them.
     * Used by "revoke all devices" to immediately invalidate any outstanding access tokens.
     */
    public function revokeAndIncrementVersionForUser(string $userId): void;
}
