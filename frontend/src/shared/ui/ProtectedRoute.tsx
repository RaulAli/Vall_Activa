import { Navigate, Outlet } from "react-router-dom";
import { useAuthStore } from "../../store/authStore";

/**
 * Renders children (or <Outlet />) only when the user is authenticated.
 * While the app is initializing (silent refresh in progress) shows a
 * full-screen spinner so we don't flash the /auth page prematurely.
 */
export function ProtectedRoute() {
    const { isAuthenticated, isInitializing } = useAuthStore();

    if (isInitializing) {
        return (
            <div className="flex h-screen items-center justify-center">
                <span className="h-8 w-8 animate-spin rounded-full border-4 border-current border-t-transparent opacity-60" />
            </div>
        );
    }

    if (!isAuthenticated) {
        return <Navigate to="/auth" replace />;
    }

    return <Outlet />;
}

/**
 * Redirects already-authenticated users away from public-only pages (e.g. /auth).
 */
export function PublicOnlyRoute() {
    const { isAuthenticated, isInitializing } = useAuthStore();

    if (isInitializing) {
        return (
            <div className="flex h-screen items-center justify-center">
                <span className="h-8 w-8 animate-spin rounded-full border-4 border-current border-t-transparent opacity-60" />
            </div>
        );
    }

    if (isAuthenticated) {
        return <Navigate to="/" replace />;
    }

    return <Outlet />;
}
