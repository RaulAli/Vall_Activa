<?php
declare(strict_types=1);

namespace App\Application\GuideAvailability\Handler;

use App\Application\GuideAvailability\Command\UpsertMyGuideAvailabilityCommand;
use App\Application\GuideAvailability\DTO\GuideAvailabilityDto;
use App\Application\GuideAvailability\Port\GuideAvailabilityRepositoryInterface;
use App\Application\User\Port\UserReadRepositoryInterface;

final class UpsertMyGuideAvailabilityHandler
{
    /** @var list<string> */
    private const DAYS = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY'];

    public function __construct(
        private readonly UserReadRepositoryInterface $users,
        private readonly GuideAvailabilityRepositoryInterface $availability,
    ) {
    }

    /** @throws \DomainException */
    public function __invoke(UpsertMyGuideAvailabilityCommand $cmd): GuideAvailabilityDto
    {
        $user = $this->users->findById($cmd->userId);

        if ($user === null) {
            throw new \DomainException('user_not_found');
        }

        if ($user->role !== 'ROLE_GUIDE') {
            throw new \DomainException('forbidden_role');
        }

        $timezone = trim((string) ($cmd->timezone ?? 'UTC'));
        if ($timezone === '') {
            $timezone = 'UTC';
        }

        if (mb_strlen($timezone) > 64) {
            throw new \DomainException('invalid_timezone');
        }

        $week = $this->normalizeWeek($cmd->week);

        return $this->availability->upsert(
            userId: $cmd->userId,
            availability: new GuideAvailabilityDto(
                timezone: $timezone,
                slotMinutes: 60,
                week: $week,
            ),
        );
    }

    /** @return list<array{day: string, enabled: bool, slots: list<string>}> */
    public static function defaultWeek(): array
    {
        return [
            ['day' => 'MONDAY', 'enabled' => true, 'slots' => ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00']],
            ['day' => 'TUESDAY', 'enabled' => true, 'slots' => ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00']],
            ['day' => 'WEDNESDAY', 'enabled' => true, 'slots' => ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00']],
            ['day' => 'THURSDAY', 'enabled' => true, 'slots' => ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00']],
            ['day' => 'FRIDAY', 'enabled' => true, 'slots' => ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00']],
            ['day' => 'SATURDAY', 'enabled' => false, 'slots' => []],
            ['day' => 'SUNDAY', 'enabled' => false, 'slots' => []],
        ];
    }

    /**
     * @param array<int, mixed> $week
     * @return list<array{day: string, enabled: bool, slots: list<string>}>
     */
    private function normalizeWeek(array $week): array
    {
        $defaults = self::defaultWeek();

        if ($week === []) {
            return $defaults;
        }

        $map = [];
        foreach ($week as $row) {
            if (!is_array($row)) {
                throw new \DomainException('invalid_week_payload');
            }

            $day = strtoupper(trim((string) ($row['day'] ?? '')));
            if (!in_array($day, self::DAYS, true)) {
                throw new \DomainException('invalid_week_payload');
            }

            $enabled = (bool) ($row['enabled'] ?? false);
            $slots = [];

            if (isset($row['slots']) && is_array($row['slots'])) {
                foreach ($row['slots'] as $slot) {
                    if (!is_string($slot)) {
                        throw new \DomainException('invalid_week_payload');
                    }

                    $normalized = trim($slot);
                    if (!preg_match('/^(?:[01]\\d|2[0-3]):[0-5]\\d$/', $normalized)) {
                        throw new \DomainException('invalid_week_payload');
                    }

                    $slots[] = $normalized;
                }
            }

            $slots = array_values(array_unique($slots));
            sort($slots);

            if ($enabled === false) {
                $slots = [];
            }

            $map[$day] = [
                'day' => $day,
                'enabled' => $enabled,
                'slots' => $slots,
            ];
        }

        $normalized = [];
        foreach ($defaults as $row) {
            $normalized[] = $map[$row['day']] ?? $row;
        }

        return $normalized;
    }
}
