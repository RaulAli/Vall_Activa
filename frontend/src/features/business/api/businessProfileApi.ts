import { http } from "../../../shared/api/http";
import { endpoints } from "../../../shared/api/endpoints";

export type BusinessProfile = {
    userId: string;
    name: string;
    slug: string;
    profileIcon: string | null;
    lat: number | null;
    lng: number | null;
    isActive: boolean;
};

export type UpdateProfilePayload = {
    name?: string;
    profileIcon?: string | null;
    lat?: number | null;
    lng?: number | null;
};

export function getMyProfile(token: string): Promise<BusinessProfile> {
    return http<BusinessProfile>("GET", endpoints.business.profile, {
        headers: { Authorization: `Bearer ${token}` },
    });
}

export function updateMyProfile(token: string, payload: UpdateProfilePayload): Promise<BusinessProfile> {
    return http<BusinessProfile>("PATCH", endpoints.business.profile, {
        headers: { Authorization: `Bearer ${token}` },
        body: payload,
    });
}
