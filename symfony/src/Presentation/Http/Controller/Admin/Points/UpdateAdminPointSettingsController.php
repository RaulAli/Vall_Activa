<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\Points;

use App\Application\Points\PointsEngine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class UpdateAdminPointSettingsController extends AbstractController
{
    #[Route('/api/admin/points/settings', name: 'admin_point_settings_patch', methods: ['PATCH'])]
    public function __invoke(Request $request, PointsEngine $pointsEngine, EntityManagerInterface $em): JsonResponse
    {
        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['error' => 'invalid_json'], 400);
        }

        $settings = $pointsEngine->getSettings();

        if (array_key_exists('pointsPerKm', $payload) && is_int($payload['pointsPerKm'])) {
            $settings->pointsPerKm = max(1, min(500, $payload['pointsPerKm']));
        }

        if (array_key_exists('dailyCapAthlete', $payload) && is_int($payload['dailyCapAthlete'])) {
            $settings->dailyCapAthlete = max(1, min(20000, $payload['dailyCapAthlete']));
        }

        if (array_key_exists('dailyCapVip', $payload) && is_int($payload['dailyCapVip'])) {
            $settings->dailyCapVip = max(1, min(20000, $payload['dailyCapVip']));
        }

        if ($settings->dailyCapVip < $settings->dailyCapAthlete) {
            $settings->dailyCapVip = $settings->dailyCapAthlete;
        }

        if (array_key_exists('vipMultiplier', $payload) && is_int($payload['vipMultiplier'])) {
            $settings->vipMultiplier = max(1, min(5, $payload['vipMultiplier']));
        }

        $settings->updatedAt = new \DateTimeImmutable();
        $em->flush();

        return $this->json([
            'pointsPerKm' => $settings->pointsPerKm,
            'dailyCapAthlete' => $settings->dailyCapAthlete,
            'dailyCapVip' => $settings->dailyCapVip,
            'vipMultiplier' => $settings->vipMultiplier,
            'updatedAt' => $settings->updatedAt->format(DATE_ATOM),
        ]);
    }
}
