import { http } from "../../../shared/api/http";
import { endpoints } from "../../../shared/api/endpoints";

export interface PublicProfile {
    slug: string;
    name: string;
    avatar: string | null;
    role: string;
    followersCount: number;
    followingCount: number;
    isFollowedByMe: boolean;
    // Role-specific
    lat?: number | null;
    lng?: number | null;
    city?: string | null;
    birthDate?: string | null;
    bio?: string | null;
    sports?: string[] | null;
}

export async function getPublicProfile(slug: string, token?: string | null): Promise<PublicProfile> {
    return http<PublicProfile>("GET", endpoints.profile.get(slug), {
        headers: token ? { Authorization: `Bearer ${token}` } : undefined,
    });
}

export async function followProfile(slug: string, token: string): Promise<void> {
    await http<null>("POST", endpoints.profile.follow(slug), {
        headers: { Authorization: `Bearer ${token}` },
    });
}

export async function unfollowProfile(slug: string, token: string): Promise<void> {
    await http<null>("DELETE", endpoints.profile.follow(slug), {
        headers: { Authorization: `Bearer ${token}` },
    });
}

export interface ProfileStub {
    slug: string;
    name: string;
    avatar: string | null;
    role: string;
}

export async function getMyFollowers(token: string): Promise<ProfileStub[]> {
    return http<ProfileStub[]>("GET", endpoints.profile.myFollowers, {
        headers: { Authorization: `Bearer ${token}` },
    });
}

export async function getMyFollowing(token: string): Promise<ProfileStub[]> {
    return http<ProfileStub[]>("GET", endpoints.profile.myFollowing, {
        headers: { Authorization: `Bearer ${token}` },
    });
}
