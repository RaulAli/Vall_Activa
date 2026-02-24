<?php
declare(strict_types=1);

namespace App\Application\Route\Command;

use App\Application\Shared\Security\Actor;

final class CreateRouteFromSourceCommand
{
    public function __construct(
        public readonly Actor $actor,

        public readonly string $title,
        public readonly string $slug,
        public readonly ?string $description,

        public readonly string $sportCode,     // ej: "hike"
        public readonly string $visibility,    // PUBLIC/UNLISTED/PRIVATE
        public readonly string $status,        // DRAFT/PUBLISHED/ARCHIVED

        public readonly string $sourceFormat,  // GPX/...
        public readonly string $sourcePath,    // ruta del archivo

        public readonly ?string $originalFilename = null,
        public readonly ?string $mimeType = null,
        public readonly ?int $fileSize = null,
        public readonly ?string $sha256 = null,
        public readonly ?string $difficulty = null,
        public readonly ?string $routeType = null,
    ) {
    }
}
