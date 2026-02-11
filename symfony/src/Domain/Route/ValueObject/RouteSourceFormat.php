<?php
declare(strict_types=1);

namespace App\Domain\Route\ValueObject;

enum RouteSourceFormat: string
{
    case GPX = 'GPX';
    case TCX = 'TCX';
    case FIT = 'FIT';
    case KML = 'KML';
    case GEOJSON = 'GEOJSON';
}
