import { env } from "../../../app/config/env";
import { http, HttpError } from "../../../shared/api/http";
import { endpoints } from "../../../shared/api/endpoints";
import type { MyRouteItem } from "../domain/types";

export interface CreateRoutePayload {
    title: string;
    slug: string;
    description?: string;
    sportCode: string;
    visibility: "PUBLIC" | "UNLISTED" | "PRIVATE";
    status: "DRAFT" | "PUBLISHED";
    file: File;
}

export async function createRoute(token: string, payload: CreateRoutePayload): Promise<string> {
    const form = new FormData();
    form.append("title", payload.title);
    form.append("slug", payload.slug);
    form.append("sportCode", payload.sportCode);
    form.append("visibility", payload.visibility);
    form.append("status", payload.status);
    form.append("sourceFormat", "GPX");
    form.append("file", payload.file);
    if (payload.description) form.append("description", payload.description);

    const url = new URL(endpoints.routes.create, env.API_BASE_URL).toString();

    const res = await fetch(url, {
        method: "POST",
        headers: { Authorization: `Bearer ${token}` },
        body: form,
    });

    if (!res.ok) {
        let body: Record<string, unknown> = {};
        try { body = await res.json(); } catch { /* empty */ }
        throw new HttpError(res.status, body, `HTTP ${res.status}`);
    }

    const data = await res.json() as { id: string };
    return data.id;
}

export async function getMyRoutes(token: string): Promise<MyRouteItem[]> {
    return http<MyRouteItem[]>("GET", endpoints.routes.mine, {
        headers: { Authorization: `Bearer ${token}` },
    });
}

export async function updateRoute(
    token: string,
    id: string,
    patch: {
        title?: string;
        description?: string | null;
        sportCode?: string;
        visibility?: "PUBLIC" | "UNLISTED" | "PRIVATE";
        status?: "DRAFT" | "PUBLISHED" | "ARCHIVED";
    }
): Promise<void> {
    await http<unknown>("PATCH", endpoints.routes.update(id), {
        headers: { Authorization: `Bearer ${token}` },
        body: patch,
    });
}
