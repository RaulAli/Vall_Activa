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
    guidePricePerHour?: number;
}

export interface ChangePasswordRequest {
    currentPassword: string;
    newPassword: string;
}
