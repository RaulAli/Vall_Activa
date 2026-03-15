import { http } from "../../../shared/api/http";
import { endpoints } from "../../../shared/api/endpoints";
import type { GuideAvailability } from "../domain/types";

export async function getMyGuideAvailability(token: string): Promise<GuideAvailability> {
    return http<GuideAvailability>("GET", endpoints.guide.availability, {
        headers: { Authorization: `Bearer ${token}` },
    });
}

export async function updateMyGuideAvailability(token: string, data: GuideAvailability): Promise<GuideAvailability> {
    return http<GuideAvailability>("PUT", endpoints.guide.availability, {
        headers: { Authorization: `Bearer ${token}` },
        body: data,
    });
}
