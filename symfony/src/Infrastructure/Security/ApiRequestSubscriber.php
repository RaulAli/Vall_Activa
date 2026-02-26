<?php
declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Auth\Port\RefreshSessionRepositoryInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Central middleware subscriber for all cross-cutting API request concerns.
 *
 * Add here anything that must run on every authenticated request:
 *  - JWT session version validation (immediate token revocation)
 *  - Future: rate limiting, security headers, request logging, etc.
 */
final class ApiRequestSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RefreshSessionRepositoryInterface $sessions,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            JWTDecodedEvent::class => 'onJWTDecoded',
        ];
    }

    /**
     * Validates that the JWT's sessionVersion matches the current session_version
     * stored in the database.
     *
     * This allows immediate revocation of access tokens even before their TTL expires:
     * incrementing session_version in DB makes all previously issued tokens invalid
     * the next time they are used — without waiting for JWT expiry.
     */
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
