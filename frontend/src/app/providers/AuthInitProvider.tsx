import { useEffect, type ReactNode } from "react";
import { useAuthStore } from "../../store/authStore";
import { refreshToken } from "../../features/auth/api/authApi";
import { onAuthBroadcast } from "../../shared/utils/authChannel";

export function AuthInitProvider({ children }: { children: ReactNode }) {
    const { token, user, setAuth, clearToken, clearAuth, setInitializing } = useAuthStore();

    useEffect(() => {
        if (token) {
            setInitializing(false);
            return;
        }

        if (!user) {
            setInitializing(false);
            return;
        }

        // AbortController prevents the React StrictMode double-invoke problem:
        // StrictMode mounts → unmounts → remounts in dev. Without this, two refresh
        // requests fire with the same cookie. The first rotates the token (A→B);
        // the second arrives with the already-rotated A, triggering revokeByFamily
        // which kills ALL sessions. The abort ensures only the second mount’s
        // request actually completes.
        const controller = new AbortController();

        refreshToken(controller.signal)
            .then((data) => setAuth(data.accessToken, user))
            .catch((err) => {
                if ((err as Error)?.name !== 'AbortError') clearToken();
            })
            .finally(() => {
                if (!controller.signal.aborted) setInitializing(false);
            });

        return () => {
            controller.abort();
            // Keep isInitializing=true until the real mount finishes.
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    useEffect(() => {
        return onAuthBroadcast((msg) => {
            if (msg === "logout") clearAuth();
        });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    return <>{children}</>;
}
