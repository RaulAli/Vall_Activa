<?php
declare(strict_types=1);

namespace App\Application\Route\Handler;

use App\Application\Route\Command\CreateRouteFromSourceCommand;
use App\Application\Route\Port\RouteParserInterface;
use App\Application\Shared\Port\TransactionInterface;
use App\Application\Shared\Security\AuthorizationServiceInterface;
use App\Domain\Identity\ValueObject\Role;
use App\Domain\Route\Entity\Route;
use App\Domain\Route\Repository\RouteRepositoryInterface;
use App\Domain\Route\ValueObject\RouteSourceFormat;
use App\Domain\Route\ValueObject\RouteStatus;
use App\Domain\Route\ValueObject\RouteVisibility;
use App\Domain\Shared\ValueObject\Uuid;
use App\Domain\Sport\Repository\SportRepositoryInterface;
use App\Domain\Sport\ValueObject\SportCode;
use App\Domain\Sport\ValueObject\SportId;
use App\Infrastructure\Persistence\Doctrine\Entity\Route\RouteSourceOrm;
use Doctrine\ORM\EntityManagerInterface;

final class CreateRouteFromSourceHandler
{
    public function __construct(
        private readonly AuthorizationServiceInterface $auth,
        private readonly TransactionInterface $tx,
        private readonly RouteRepositoryInterface $routes,
        private readonly SportRepositoryInterface $sports,
        private readonly RouteParserInterface $parser,
        private readonly EntityManagerInterface $em
    ) {
    }

    public function __invoke(CreateRouteFromSourceCommand $cmd): string
    {
        // Permisos: GUIDE o ATHLETE o ADMIN
        if (!$cmd->actor->isAdmin()) {
            $isGuide = $cmd->actor->has(Role::GUIDE);
            $isAthlete = $cmd->actor->has(Role::ATHLETE);
            if (!$isGuide && !$isAthlete) {
                $this->auth->requireRole($cmd->actor, Role::GUIDE);
            }
        }

        $format = RouteSourceFormat::tryFrom($cmd->sourceFormat);
        if ($format === null || !$this->parser->supports($format)) {
            throw new \InvalidArgumentException('Unsupported route format.');
        }

        $visibility = RouteVisibility::tryFrom($cmd->visibility) ?? RouteVisibility::PRIVATE;
        $status = RouteStatus::tryFrom($cmd->status) ?? RouteStatus::DRAFT;

        $sport = $this->sports->findActiveByCode(SportCode::fromString($cmd->sportCode));
        if ($sport === null) {
            throw new \InvalidArgumentException('Sport not found or inactive.');
        }
        $sportId = SportId::fromString($sport->id()->value());

        return $this->tx->run(function () use ($cmd, $format, $visibility, $status, $sportId): string {
            $parsed = $this->parser->parse($format, $cmd->sourcePath);

            $route = Route::createFromParsed(
                createdByUserId: $cmd->actor->userId->value(),
                sportId: $sportId,
                title: $cmd->title,
                slug: $cmd->slug,
                description: $cmd->description,
                visibility: $visibility,
                status: $status,
                startLat: $parsed->startLat,
                startLng: $parsed->startLng,
                endLat: $parsed->endLat,
                endLng: $parsed->endLng,
                minLat: $parsed->minLat,
                minLng: $parsed->minLng,
                maxLat: $parsed->maxLat,
                maxLng: $parsed->maxLng,
                distanceM: $parsed->distanceM,
                elevationGainM: $parsed->elevationGainM,
                elevationLossM: $parsed->elevationLossM,
                polyline: $parsed->polyline,
                durationSeconds: $parsed->durationSeconds,
                difficulty: $cmd->difficulty,
                routeType: $cmd->routeType,
            );

            $this->routes->save($route);

            $src = new RouteSourceOrm();
            $src->id = Uuid::v4()->value();
            $src->routeId = $route->id()->value();
            $src->format = $format->value;
            $src->originalFilename = $cmd->originalFilename;
            $src->mimeType = $cmd->mimeType;
            $src->filePath = $cmd->sourcePath;
            $src->fileSize = $cmd->fileSize;
            $src->sha256 = $cmd->sha256;

            $now = new \DateTimeImmutable();
            $src->uploadedAt = $now;
            $src->parsedAt = $now;
            $src->parseStatus = 'OK';
            $src->parseError = null;
            $src->parserVersion = 'v1';

            $this->em->persist($src);

            return $route->id()->value();
        });
    }
}
