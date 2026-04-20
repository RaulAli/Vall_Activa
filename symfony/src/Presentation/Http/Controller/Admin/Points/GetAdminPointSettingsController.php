<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\Points;

use App\Application\Points\PointsEngine;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class GetAdminPointSettingsController extends AbstractController
{
    #[Route('/api/admin/points/settings', name: 'admin_point_settings_get', methods: ['GET'])]
    public function __invoke(PointsEngine $pointsEngine): JsonResponse
    {
        $settings = $pointsEngine->getSettings();

        return $this->json([
            'pointsPerKm' => $settings->pointsPerKm,
            'dailyCapAthlete' => $settings->dailyCapAthlete,
            'dailyCapVip' => $settings->dailyCapVip,
            'vipMultiplier' => $settings->vipMultiplier,
            'updatedAt' => $settings->updatedAt->format(DATE_ATOM),
        ]);
    }
}
