<?php
declare(strict_types=1);

namespace App\Application\Profile\Command;

final class UnfollowProfileCommand
{
    public function __construct(
        public readonly string $followerId,
        public readonly string $slug,
    ) {
    }
}
