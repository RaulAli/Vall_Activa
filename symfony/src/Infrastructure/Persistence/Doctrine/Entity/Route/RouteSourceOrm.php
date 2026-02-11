<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity\Route;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'route_sources')]
#[ORM\Index(name: 'idx_route_sources_route', columns: ['route_id'])]
#[ORM\Index(name: 'idx_route_sources_sha', columns: ['sha256'])]
class RouteSourceOrm
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(name: 'route_id', type: 'string', length: 36)]
    public string $routeId;

    #[ORM\Column(type: 'string', length: 30)]
    public string $format;

    #[ORM\Column(name: 'original_filename', type: 'string', length: 255, nullable: true)]
    public ?string $originalFilename = null;

    #[ORM\Column(name: 'mime_type', type: 'string', length: 255, nullable: true)]
    public ?string $mimeType = null;

    #[ORM\Column(name: 'file_path', type: 'string', length: 2048)]
    public string $filePath;

    #[ORM\Column(name: 'file_size', type: 'integer', nullable: true)]
    public ?int $fileSize = null;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    public ?string $sha256 = null;

    #[ORM\Column(name: 'uploaded_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $uploadedAt;

    #[ORM\Column(name: 'parsed_at', type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $parsedAt = null;

    #[ORM\Column(name: 'parse_status', type: 'string', length: 20)]
    public string $parseStatus = 'PENDING';

    #[ORM\Column(name: 'parse_error', type: 'text', nullable: true)]
    public ?string $parseError = null;

    #[ORM\Column(name: 'parser_version', type: 'string', length: 50, nullable: true)]
    public ?string $parserVersion = null;
}
