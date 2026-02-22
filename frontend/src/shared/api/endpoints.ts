export const endpoints = {
    auth: {
        login: "/api/auth/login",
        register: "/api/auth/register",
        logout: "/api/auth/logout",
        refresh: "/api/auth/token/refresh",
        me: "/api/user/me",
    },
    offers: {
        list: "/api/public/offers",
        filters: "/api/public/offers/filters",
        mapBusinesses: "/api/public/offers/map-businesses",
        details: (slug: string) => `/api/public/offers/${slug}`,
    },
    routes: {
        list: "/api/public/routes",
        filters: "/api/public/routes/filters",
        mapMarkers: "/api/public/routes/map-markers",
        details: (slug: string) => `/api/public/routes/${slug}`,
    },
};
