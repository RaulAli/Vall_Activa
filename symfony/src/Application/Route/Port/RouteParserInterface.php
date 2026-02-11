<?php
declare(strict_types=1);

namespace App\Application\Route\Port;

use App\Application\Route\DTO\ParsedRouteData;
use App\Domain\Route\ValueObject\RouteSourceFormat;

interface RouteParserInterface
{
    public function supports(RouteSourceFormat $format): bool;
    public function parse(RouteSourceFormat $format, string $absolutePath): ParsedRouteData;
}
