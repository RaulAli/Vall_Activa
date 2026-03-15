import { createBrowserRouter } from "react-router-dom";
import { HomePage } from "../../pages/HomePage";
import { ShopPage } from "../../pages/ShopPage";
import { RouteDetailPage } from "../../pages/RouteDetailPage";
import { OfferDetailPage } from "../../pages/OfferDetailPage";
import { AuthPage } from "../../pages/AuthPage";
import { ProfilePage } from "../../pages/ProfilePage";
import { PublicProfilePage } from "../../pages/PublicProfilePage";
import { SettingsPage } from "../../pages/SettingsPage";
import { CreateRoutePage } from "../../pages/CreateRoutePage";
import { MyRoutesPage } from "../../pages/MyRoutesPage";
import { MyBookingsPage } from "../../pages/MyBookingsPage";
import { CreateOfferPage } from "../../pages/CreateOfferPage";
import { MyOffersPage } from "../../pages/MyOffersPage";
import { AdminPage } from "../../pages/AdminPage";
import { GuideDashboardPage } from "../../pages/GuideDashboardPage";
import { MarketplacePage } from "../../pages/MarketplacePage";
import { ProtectedRoute, PublicOnlyRoute } from "../../shared/ui/ProtectedRoute";

export const router = createBrowserRouter([
    // ── Public routes ──────────────────────────────────────────────────────
    { path: "/", element: <HomePage /> },
    { path: "/marketplace", element: <MarketplacePage /> },
    { path: "/offers", element: <ShopPage /> },
    { path: "/routes", element: <ShopPage /> },
    { path: "/route/:slug", element: <RouteDetailPage /> },
    { path: "/offer/:slug", element: <OfferDetailPage /> },
    { path: "/profile/:slug", element: <PublicProfilePage /> },

    // ── Public-only (redirect to / if already logged in) ──────────────────
    {
        element: <PublicOnlyRoute />,
        children: [
            { path: "/auth", element: <AuthPage /> },
        ],
    },

    // ── Protected (redirect to /auth if not logged in) ────────────────────
    {
        element: <ProtectedRoute />,
        children: [
            { path: "/me", element: <ProfilePage /> },
            { path: "/settings", element: <SettingsPage /> },
            { path: "/routes/new", element: <CreateRoutePage /> },
            { path: "/me/routes", element: <MyRoutesPage /> },
            { path: "/me/bookings", element: <MyBookingsPage /> },
            { path: "/offers/new", element: <CreateOfferPage /> },
            { path: "/me/offers", element: <MyOffersPage /> },
            { path: "/guide/dashboard", element: <GuideDashboardPage /> },
            { path: "/admin", element: <AdminPage /> },
        ],
    },
]);
