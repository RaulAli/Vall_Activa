export type RouteListItem = {
    id: string;
    sportId: string;
    title: string;
    slug: string;

    // list v2
    startLat: number | null;
    startLng: number | null;

    distanceM: number;
    elevationGainM: number;
    elevationLossM: number;

    isActive: boolean;
    createdAt: string;
};

export type PaginatedResult<T> = {
    page: number;
    limit: number;
    total: number;
    items: T[];
};

export type RouteFiltersMeta = {
    distance: { min: number | null; max: number | null };
    gain: { min: number | null; max: number | null };
    sports: { code: string; name: string; count: number }[];
    counts: { routes: number };
    bounds: { minLng: number; minLat: number; maxLng: number; maxLat: number } | null;
};

export type RouteMapMarker = {
    slug: string;
    title: string;
    lat: number;
    lng: number;
};

export type RouteMapMarkersResponse = {
    items: RouteMapMarker[];
};


