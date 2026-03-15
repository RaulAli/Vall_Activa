import type { GuideAvailability, GuideDayAvailability, WeekdayKey } from "../domain/types";

const STORAGE_PREFIX = "vamo:guide:availability";

const WEEK_DAYS: WeekdayKey[] = [
    "MONDAY",
    "TUESDAY",
    "WEDNESDAY",
    "THURSDAY",
    "FRIDAY",
    "SATURDAY",
    "SUNDAY",
];

const DEFAULT_WEEKDAY_SLOTS = ["09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00"];

function defaultDay(day: WeekdayKey): GuideDayAvailability {
    return {
        day,
        enabled: day !== "SATURDAY" && day !== "SUNDAY",
        slots: day !== "SATURDAY" && day !== "SUNDAY" ? [...DEFAULT_WEEKDAY_SLOTS] : [],
    };
}

export function createDefaultAvailability(): GuideAvailability {
    return {
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || "UTC",
        slotMinutes: 60,
        week: WEEK_DAYS.map(defaultDay),
    };
}

function storageKey(userId: string): string {
    return `${STORAGE_PREFIX}:${userId}`;
}

export function loadGuideAvailability(userId: string): GuideAvailability {
    const raw = localStorage.getItem(storageKey(userId));
    if (!raw) return createDefaultAvailability();

    try {
        const parsed = JSON.parse(raw) as GuideAvailability;
        if (!parsed || !Array.isArray(parsed.week) || parsed.slotMinutes !== 60) {
            return createDefaultAvailability();
        }

        const map = new Map(parsed.week.map((d) => [d.day, d]));
        const normalizedWeek = WEEK_DAYS.map((day) => {
            const row = map.get(day);
            return {
                day,
                enabled: row?.enabled ?? false,
                slots: Array.isArray(row?.slots)
                    ? row!.slots.filter((s): s is string => typeof s === "string")
                    : [],
            };
        });

        return {
            timezone: parsed.timezone || Intl.DateTimeFormat().resolvedOptions().timeZone || "UTC",
            slotMinutes: 60,
            week: normalizedWeek,
        };
    } catch {
        return createDefaultAvailability();
    }
}

export function saveGuideAvailability(userId: string, value: GuideAvailability): void {
    localStorage.setItem(storageKey(userId), JSON.stringify(value));
}
