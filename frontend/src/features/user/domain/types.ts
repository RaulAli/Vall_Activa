export interface UpdateMeRequest {
    name?: string;
    slug?: string;
    avatar?: string | null;
    // Business
    lat?: number | null;
    lng?: number | null;
    // Athlete
    city?: string | null;
    birthDate?: string | null;
    // Guide
    bio?: string | null;
    sports?: string[];
}

export interface ChangePasswordRequest {
    currentPassword: string;
    newPassword: string;
}
