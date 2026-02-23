export type OfferListItem = {
    id: string;
    businessId: string;
    title: string;
    slug: string;
    description: string | null;
    price: string;
    currency: string;
    image: string | null;
    discountType: string;
    status: string;
    quantity: number;
    pointsCost: number;
    isActive: boolean;
    createdAt: string;
    businessName?: string | null;
    businessSlug?: string | null;
    businessAvatar?: string | null;
};

export type PaginatedResult<T> = {
    page: number;
    limit: number;
    total: number;
    items: T[];
};

export type OfferFiltersMeta = {
    price: { min: string | null; max: string | null };
    points: { min: number | null; max: number | null };
    discountTypes: { value: string; count: number }[];
    counts: { offers: number; businesses: number };
    bounds: { minLng: number; minLat: number; maxLng: number; maxLat: number } | null;
};

export type BusinessMapMarker = {
    businessUserId: string;
    slug: string;
    name: string;
    lat: number | null;
    lng: number | null;
    profileIcon: string | null;
    offersCount: number;
};

export type MyOfferItem = {
    id: string;
    title: string;
    slug: string;
    description: string | null;
    price: string;
    currency: string;
    image: string | null;
    discountType: "AMOUNT" | "PERCENT" | "NONE";
    status: "DRAFT" | "PUBLISHED" | "ARCHIVED";
    quantity: number;
    pointsCost: number;
    isActive: boolean;
    createdAt: string;
};

export interface CreateOfferPayload {
    title: string;
    slug: string;
    description?: string;
    price: string;
    currency: string;
    isActive: boolean;
    quantity: number;
    pointsCost: number;
    image?: string;
    discountType: "AMOUNT" | "PERCENT" | "NONE";
    status: "DRAFT" | "PUBLISHED";
}
