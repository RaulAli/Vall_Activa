export type AuthRole = "ROLE_BUSINESS" | "ROLE_ATHLETE" | "ROLE_GUIDE";

export interface LoginRequest {
    email: string;
    password: string;
}

export interface RegisterRequest {
    email: string;
    password: string;
    role: AuthRole;
    name: string;
    slug: string;
}

export interface AuthResponse {
    accessToken: string;
    userId: string;
    email: string;
}

export interface AuthUser {
    id: string;
    email: string;
    role: string;
    createdAt: string;
    slug: string | null;
    name: string | null;
    avatar: string | null;
    // Role-specific
    lat?: number | null;
    lng?: number | null;
    city?: string | null;
    birthDate?: string | null;
    bio?: string | null;
    sports?: string[] | null;
}
