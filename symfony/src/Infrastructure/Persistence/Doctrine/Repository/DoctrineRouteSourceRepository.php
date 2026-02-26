<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Application\Route\Port\RouteSourceRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\Route\RouteSourceOrm;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineRouteSourceRepository implements RouteSourceRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function save(array $data): void
    {
        $src = new RouteSourceOrm();
        $src->id = $data['id'];
        $src->routeId = $data['routeId'];
        $src->format = $data['format'];
        $src->originalFilename = $data['originalFilename'];
        $src->mimeType = $data['mimeType'];
        $src->filePath = $data['filePath'];
        $src->fileSize = $data['fileSize'];
        $src->sha256 = $data['sha256'];
        $src->uploadedAt = $data['uploadedAt'];
        $src->parsedAt = $data['parsedAt'];
        $src->parseStatus = $data['parseStatus'];
        $src->parseError = $data['parseError'];
        $src->parserVersion = $data['parserVersion'];

        $this->em->persist($src);
    }
}
