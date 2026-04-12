import { endpoints } from "../../../shared/api/endpoints";
import { http } from "../../../shared/api/http";
import type {
    AdminIncident,
    AdminIncidentCategory,
    CreateIncidentRequest,
    IncidentCategory,
    UserIncident,
} from "../domain/types";

export async function createIncident(token: string, data: CreateIncidentRequest): Promise<{ id: string }> {
    return http<{ id: string }>("POST", endpoints.incidents.create, {
        headers: { Authorization: `Bearer ${token}` },
        body: data,
    });
}

export async function listAdminIncidents(token: string): Promise<AdminIncident[]> {
    return http<AdminIncident[]>("GET", endpoints.admin.incidents, {
        headers: { Authorization: `Bearer ${token}` },
    });
}

export async function updateAdminIncidentStatus(token: string, id: string, status: "OPEN" | "REVIEWING" | "RESOLVED"): Promise<{ id: string; status: string }> {
    return http<{ id: string; status: string }>("PATCH", endpoints.admin.updateIncidentStatus(id), {
        headers: { Authorization: `Bearer ${token}` },
        body: { status },
    });
}

export async function listIncidentCategories(token: string): Promise<IncidentCategory[]> {
    return http<IncidentCategory[]>("GET", endpoints.incidents.categories, {
        headers: { Authorization: `Bearer ${token}` },
    });
}

export async function listMyIncidents(token: string): Promise<UserIncident[]> {
    return http<UserIncident[]>("GET", endpoints.incidents.mine, {
        headers: { Authorization: `Bearer ${token}` },
    });
}

export async function closeMyIncident(token: string, id: string): Promise<{ id: string; status: string }> {
    return http<{ id: string; status: string }>("PATCH", endpoints.incidents.close(id), {
        headers: { Authorization: `Bearer ${token}` },
    });
}

export async function listAdminIncidentCategories(token: string): Promise<AdminIncidentCategory[]> {
    return http<AdminIncidentCategory[]>("GET", endpoints.admin.incidentCategories, {
        headers: { Authorization: `Bearer ${token}` },
    });
}

export async function createAdminIncidentCategory(token: string, data: { code: string; name: string }): Promise<AdminIncidentCategory> {
    return http<AdminIncidentCategory>("POST", endpoints.admin.createIncidentCategory, {
        headers: { Authorization: `Bearer ${token}` },
        body: data,
    });
}

export async function updateAdminIncidentCategory(token: string, id: string, data: { code?: string; name?: string }): Promise<AdminIncidentCategory> {
    return http<AdminIncidentCategory>("PATCH", endpoints.admin.updateIncidentCategory(id), {
        headers: { Authorization: `Bearer ${token}` },
        body: data,
    });
}

export async function toggleAdminIncidentCategory(token: string, id: string): Promise<{ id: string; isActive: boolean }> {
    return http<{ id: string; isActive: boolean }>("PATCH", endpoints.admin.toggleIncidentCategory(id), {
        headers: { Authorization: `Bearer ${token}` },
    });
}
