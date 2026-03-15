export type WeekdayKey = "MONDAY" | "TUESDAY" | "WEDNESDAY" | "THURSDAY" | "FRIDAY" | "SATURDAY" | "SUNDAY";

export interface GuideDayAvailability {
    day: WeekdayKey;
    enabled: boolean;
    slots: string[];
}

export interface GuideAvailability {
    timezone: string;
    slotMinutes: 60;
    week: GuideDayAvailability[];
}
