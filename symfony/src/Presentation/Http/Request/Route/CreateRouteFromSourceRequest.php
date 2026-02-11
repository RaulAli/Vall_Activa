<?php
declare(strict_types=1);

namespace App\Presentation\Http\Request\Route;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateRouteFromSourceRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $title,

        #[Assert\NotBlank]
        public string $slug,

        public ?string $description = null,

        #[Assert\NotBlank]
        public string $sportCode, // ej: "hike"

        #[Assert\Choice(choices: ['PUBLIC', 'UNLISTED', 'PRIVATE'])]
        public string $visibility = 'PRIVATE',

        #[Assert\Choice(choices: ['DRAFT', 'PUBLISHED', 'ARCHIVED'])]
        public string $status = 'DRAFT',

        #[Assert\Choice(choices: ['GPX', 'TCX', 'FIT', 'KML', 'GEOJSON'])]
        public string $sourceFormat = 'GPX',

        #[Assert\NotBlank]
        public string $sourcePath = '',

        public ?string $originalFilename = null,
        public ?string $mimeType = null,
        public ?int $fileSize = null,
        public ?string $sha256 = null
    ) {
    }
}
