export interface CreateIncidentRequest {
    category: string;
    subject: string;
    message: string;
}

export interface AdminIncident {
    id: string;
    userId: string;
    userEmail: string | null;
    category: string;
    subject: string;
    message: string;
    status: "OPEN" | "REVIEWING" | "RESOLVED";
    createdAt: string;
    updatedAt?: string;
}

export interface UserIncident {
    id: string;
    category: string;
    subject: string;
    message: string;
    status: "OPEN" | "REVIEWING" | "RESOLVED";
    createdAt: string;
    updatedAt: string;
}

export interface IncidentCategory {
    code: string;
    name: string;
}

export interface AdminIncidentCategory {
    id: string;
    code: string;
    name: string;
    isActive: boolean;
    createdAt: string;
}
