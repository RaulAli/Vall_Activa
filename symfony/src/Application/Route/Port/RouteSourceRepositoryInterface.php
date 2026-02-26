<?php
declare(strict_types=1);

namespace App\Application\Route\Port;

interface RouteSourceRepositoryInterface
{
    /**
     * @param array{
     *   id: string,
     *   routeId: string,
     *   format: string,
     *   originalFilename: string|null,
     *   mimeType: string|null,
     *   filePath: string,
     *   fileSize: int|null,
     *   sha256: string|null,
     *   uploadedAt: \DateTimeImmutable,
     *   parsedAt: \DateTimeImmutable|null,
     *   parseStatus: string,
     *   parseError: string|null,
     *   parserVersion: string|null,
     * } $data
     */
    public function save(array $data): void;
}
