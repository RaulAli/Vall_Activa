import { useEffect, type ReactNode } from "react";
import { useAuthStore } from "../../store/authStore";
import { refreshToken } from "../../features/auth/api/authApi";
import { onAuthBroadcast } from "../../shared/utils/authChannel";

export function AuthInitProvider({ children }: { children: ReactNode }) {
    const { token, user, setAuth, clearAuth, setInitializing } = useAuthStore();

    useEffect(() => {
        if (token) {
            setInitializing(false);
            return;
        }

        if (!user) {
            setInitializing(false);
            return;
        }

        refreshToken()
            .then((data) => setAuth(data.accessToken, user))
            .catch(() => clearAuth())
            .finally(() => setInitializing(false));
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
