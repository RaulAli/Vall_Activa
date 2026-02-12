export const endpoints = {
    offers: {
        list: "/api/public/offers",
        filters: "/api/public/offers/filters",
        mapBusinesses: "/api/public/offers/map-businesses",
        details: (slug: string) => `/api/public/offers/${slug}`,
    },
    routes: {
        list: "/api/public/routes",
        filters: "/api/public/routes/filters",
        details: (slug: string) => `/api/public/routes/${slug}`,
    },
};
