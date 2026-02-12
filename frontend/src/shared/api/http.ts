import { env } from "../../app/config/env";

type HttpMethod = "GET" | "POST" | "PUT" | "PATCH" | "DELETE";

function buildUrl(path: string, query?: Record<string, string | number | boolean | null | undefined>) {
    const url = new URL(path, env.API_BASE_URL);
    if (query) {
        for (const [k, v] of Object.entries(query)) {
            if (v === null || v === undefined || v === "") continue;
            url.searchParams.set(k, String(v));
        }
    }
    return url.toString();
}

export async function http<T>(
    method: HttpMethod,
    path: string,
    opts?: {
        query?: Record<string, string | number | boolean | null | undefined>;
        body?: unknown;
        signal?: AbortSignal;
    }
): Promise<T> {
    const url = buildUrl(path, opts?.query);

    const res = await fetch(url, {
        method,
        headers: {
            "Content-Type": "application/json",
        },
        body: opts?.body ? JSON.stringify(opts.body) : undefined,
        signal: opts?.signal,
    });

    if (!res.ok) {
        const text = await res.text().catch(() => "");
        throw new Error(`HTTP ${res.status}: ${text || res.statusText}`);
    }

    return (await res.json()) as T;
}
