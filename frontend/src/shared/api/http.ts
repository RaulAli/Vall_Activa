import { env } from "../../app/config/env";

type HttpMethod = "GET" | "POST" | "PUT" | "PATCH" | "DELETE";

export class HttpError extends Error {
    constructor(
        public readonly status: number,
        public readonly body: Record<string, unknown>,
        message: string,
    ) {
        super(message);
        this.name = "HttpError";
    }
}

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

// Singleton promise: only one refresh runs at a time even with concurrent 401s.
let _refreshPromise: Promise<string | null> | null = null;

async function attemptSilentRefresh(): Promise<string | null> {
    if (_refreshPromise) return _refreshPromise;

    _refreshPromise = (async () => {
        try {
            const res = await fetch(buildUrl("/api/auth/refresh"), {
                method: "POST",
                credentials: "include",
            });

            if (!res.ok) throw new Error("refresh_failed");

            const data = (await res.json()) as { accessToken: string; userId: string; email: string };

            const { useAuthStore } = await import("../../store/authStore");
            const currentUser = useAuthStore.getState().user;
            if (currentUser) {
                useAuthStore.getState().setAuth(data.accessToken, currentUser);
            }

            return data.accessToken;
        } catch {
            const { useAuthStore } = await import("../../store/authStore");
            useAuthStore.getState().clearAuth();
            window.location.href = "/auth";
            return null;
        } finally {
            _refreshPromise = null;
        }
    })();

    return _refreshPromise;
}

export async function http<T>(
    method: HttpMethod,
    path: string,
    opts?: {
        query?: Record<string, string | number | boolean | null | undefined>;
        body?: unknown;
        signal?: AbortSignal;
        headers?: Record<string, string>;
        /** Send cookies with the request (needed for refresh-token cookie). Default: false */
        withCredentials?: boolean;
    },
    _retried = false,
): Promise<T> {
    const url = buildUrl(path, opts?.query);

    const res = await fetch(url, {
        method,
        headers: {
            "Content-Type": "application/json",
            ...opts?.headers,
        },
        credentials: opts?.withCredentials ? "include" : "omit",
        body: opts?.body ? JSON.stringify(opts.body) : undefined,
        signal: opts?.signal,
    });

    if (!res.ok) {
        let body: Record<string, unknown> = {};
        try { body = await res.json(); } catch { /* empty */ }

        if (res.status === 401 && !_retried && !path.startsWith("/api/auth/")) {
            const newToken = await attemptSilentRefresh();

            if (newToken) {
                const updatedHeaders: Record<string, string> = {
                    ...opts?.headers,
                    ...(opts?.headers?.["Authorization"]
                        ? { Authorization: `Bearer ${newToken}` }
                        : {}),
                };
                return http(method, path, { ...opts, headers: updatedHeaders }, true);
            }
        }

        if (res.status === 401) {
            const { useAuthStore } = await import("../../store/authStore");
            useAuthStore.getState().clearAuth();
            window.location.href = "/auth";
        }

        throw new HttpError(res.status, body, `HTTP ${res.status}`);
    }

    // 204 No Content
    if (res.status === 204) return undefined as T;

    return (await res.json()) as T;
}

