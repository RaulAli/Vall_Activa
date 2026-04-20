import { http } from "../../../shared/api/http";
import { endpoints } from "../../../shared/api/endpoints";

export interface AthletePointsSummary {
    balance: number;
    todayEarned: number;
    dailyCap: number;
    remainingToday: number;
    pointsPerKm: number;
    vipMultiplier: number;
    isVipForPoints: boolean;
}

export interface PublicPointSettings {
    pointsPerKm: number;
}

export interface AthletePointMission {
    id: string;
    code: string;
    title: string;
    description: string | null;
    pointsReward: number;
    completedToday: boolean;
    auto?: boolean;
    progress?: {
        current: number;
        target: number;
        unit: "KM" | "COUNT";
    } | null;
}

export interface CompleteMissionResponse {
    missionId: string;
    awarded: number;
    requested: number;
    today: number;
    cap: number;
    balance: number;
    multiplier: number;
}

export async function getAthletePointsSummary(token: string): Promise<AthletePointsSummary> {
    return http<AthletePointsSummary>("GET", endpoints.athlete.pointsSummary, {
        headers: { Authorization: `Bearer ${token}` },
    });
}

export async function getPublicPointSettings(): Promise<PublicPointSettings> {
    return http<PublicPointSettings>("GET", endpoints.points.settings);
}

export async function getAthletePointMissions(token: string): Promise<AthletePointMission[]> {
    return http<AthletePointMission[]>("GET", endpoints.athlete.pointsMissions, {
        headers: { Authorization: `Bearer ${token}` },
    });
}

export async function completeAthletePointMission(token: string, missionId: string): Promise<CompleteMissionResponse> {
    return http<CompleteMissionResponse>("POST", endpoints.athlete.completeMission(missionId), {
        headers: { Authorization: `Bearer ${token}` },
    });
}

export interface AdminPointSettings {
    pointsPerKm: number;
    dailyCapAthlete: number;
    dailyCapVip: number;
    vipMultiplier: number;
    updatedAt: string;
}

export interface AdminPointMission {
    id: string;
    code: string;
    title: string;
    description: string | null;
    pointsReward: number;
    isActive: boolean;
    createdAt: string;
    updatedAt: string;
}

export async function getAdminPointSettings(token: string): Promise<AdminPointSettings> {
    return http<AdminPointSettings>("GET", endpoints.admin.pointSettings, {
        headers: { Authorization: `Bearer ${token}` },
    });
}

export async function updateAdminPointSettings(token: string, body: Partial<AdminPointSettings>): Promise<AdminPointSettings> {
    return http<AdminPointSettings>("PATCH", endpoints.admin.pointSettings, {
        headers: { Authorization: `Bearer ${token}` },
        body,
    });
}

export async function getAdminPointMissions(token: string): Promise<AdminPointMission[]> {
    return http<AdminPointMission[]>("GET", endpoints.admin.pointMissions, {
        headers: { Authorization: `Bearer ${token}` },
    });
}

export async function createAdminPointMission(
    token: string,
    body: { code: string; title: string; description?: string; pointsReward: number },
): Promise<AdminPointMission> {
    return http<AdminPointMission>("POST", endpoints.admin.pointMissions, {
        headers: { Authorization: `Bearer ${token}` },
        body,
    });
}

export async function updateAdminPointMission(
    token: string,
    missionId: string,
    body: Partial<{ code: string; title: string; description: string | null; pointsReward: number }>,
): Promise<AdminPointMission> {
    return http<AdminPointMission>("PATCH", endpoints.admin.updatePointMission(missionId), {
        headers: { Authorization: `Bearer ${token}` },
        body,
    });
}

export async function toggleAdminPointMission(token: string, missionId: string): Promise<{ id: string; isActive: boolean }> {
    return http<{ id: string; isActive: boolean }>("PATCH", endpoints.admin.togglePointMission(missionId), {
        headers: { Authorization: `Bearer ${token}` },
    });
}
