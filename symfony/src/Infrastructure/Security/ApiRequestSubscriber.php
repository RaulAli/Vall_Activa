<?php
declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Auth\Port\RefreshSessionRepositoryInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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

    public function onJWTDecoded(JWTDecodedEvent $event): void
    {
        $payload = $event->getPayload();

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
