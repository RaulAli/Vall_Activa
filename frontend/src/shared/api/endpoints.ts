export const endpoints = {
    auth: {
        login: "/api/auth/login",
        register: "/api/auth/register",
        logout: "/api/auth/logout",
        refresh: "/api/auth/token/refresh",
        me: "/api/user/me",
    },
    user: {
        me: "/api/user/me",
        password: "/api/user/me/password",
    },
    offers: {
        list: "/api/public/offers",
        filters: "/api/public/offers/filters",
        mapBusinesses: "/api/public/offers/map-businesses",
        details: (slug: string) => `/api/public/offers/${slug}`,
        mine: "/api/business/me/offers",
        update: (id: string) => `/api/business/me/offers/${id}`,
    },
    routes: {
        list: "/api/public/routes",
        filters: "/api/public/routes/filters",
        mapMarkers: "/api/public/routes/map-markers",
        details: (slug: string) => `/api/public/routes/${slug}`,
        create: "/api/routes",
        update: (id: string) => `/api/routes/${id}`,
        mine: "/api/me/routes",
    },
    sports: {
        list: "/api/public/sports",
    },
    profile: {
        get: (slug: string) => `/api/profile/${slug}`,
        follow: (slug: string) => `/api/profile/${slug}/follow`,
        myFollowers: "/api/me/followers",
        myFollowing: "/api/me/following",
    },
    business: {
        profile: "/api/business/me/profile",
    },
};
