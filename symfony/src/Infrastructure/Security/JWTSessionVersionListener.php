<?php
declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Auth\Port\RefreshSessionRepositoryInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;

/**
 * Validates that the JWT's sessionVersion matches the current session_version
 * stored in the database.
 *
 * This allows immediate revocation of access tokens even before their TTL expires:
 * incrementing session_version in DB makes all previously issued tokens invalid
 * the next time they are used — without waiting for JWT expiry.
 */
final class JWTSessionVersionListener
{
    public function __construct(
        private readonly RefreshSessionRepositoryInterface $sessions,
    ) {
    }

    public function onJWTDecoded(JWTDecodedEvent $event): void
    {
        $payload = $event->getPayload();

        // Tokens without sessionId/sessionVersion are issued by old code — reject
        if (!isset($payload['sessionId'], $payload['sessionVersion'])) {
            $event->markAsInvalid();
            return;
        }

        $session = $this->sessions->findById((string) $payload['sessionId']);

        if ($session === null || $session['revoked'] === true) {
            $event->markAsInvalid();
            return;
        }

        if ((int) $payload['sessionVersion'] !== $session['sessionVersion']) {
            $event->markAsInvalid();
            return;
        }
    }
}
