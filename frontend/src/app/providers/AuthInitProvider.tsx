import { useEffect, type ReactNode } from "react";
import { useAuthStore } from "../../store/authStore";
import { refreshToken } from "../../features/auth/api/authApi";

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

    return <>{children}</>;
}
