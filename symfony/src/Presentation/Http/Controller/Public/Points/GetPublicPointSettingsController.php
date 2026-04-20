<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Public\Points;

use App\Application\Points\PointsEngine;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class GetPublicPointSettingsController extends AbstractController
{
    #[Route('/api/public/points/settings', name: 'public_points_settings', methods: ['GET'])]
    public function __invoke(PointsEngine $pointsEngine): JsonResponse
    {
        $settings = $pointsEngine->getSettings();

        return $this->json([
            'pointsPerKm' => $settings->pointsPerKm,
        ]);
    }
}
