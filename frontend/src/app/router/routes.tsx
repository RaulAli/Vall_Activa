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

export const router = createBrowserRouter([
    { path: "/", element: <HomePage /> },
    { path: "/auth", element: <AuthPage /> },
    { path: "/me", element: <ProfilePage /> },
    { path: "/settings", element: <SettingsPage /> },
    { path: "/profile/:slug", element: <PublicProfilePage /> },
    { path: "/offers", element: <ShopPage /> },
    { path: "/routes", element: <ShopPage /> },
    { path: "/routes/new", element: <CreateRoutePage /> },
    { path: "/route/:slug", element: <RouteDetailPage /> },
    { path: "/offer/:slug", element: <OfferDetailPage /> },
]);
