import { create } from "zustand";
import type { Bbox } from "../shared/utils/bbox";

export type ShopTab = "offers" | "routes";

export type OffersUiState = {
    q: string;
    discountType: string | null;
    inStock: boolean;
    sort: "recent" | "price" | "points";
    order: "asc" | "desc";
    page: number;
    limit: number;
};

export type RoutesUiState = {
    q: string;
    sportCode: string | null;
    distanceMin: number | null;
    distanceMax: number | null;
    gainMin: number | null;
    gainMax: number | null;
    sort: "recent" | "distance" | "gain";
    order: "asc" | "desc";
    page: number;
    limit: number;
};

type ShopState = {
    tab: ShopTab;
    bbox: Bbox | null;

    offers: OffersUiState;
    routes: RoutesUiState;

    setTab: (tab: ShopTab) => void;
    setBbox: (bbox: Bbox | null) => void;

    setOffers: (patch: Partial<OffersUiState>) => void;
    resetOffersPage: () => void;

    setRoutes: (patch: Partial<RoutesUiState>) => void;
    resetRoutesPage: () => void;

    resetAll: () => void;
};

const defaultOffers: OffersUiState = {
    q: "",
    discountType: null,
    inStock: false,
    sort: "recent",
    order: "desc",
    page: 1,
    limit: 20,
};

const defaultRoutes: RoutesUiState = {
    q: "",
    sportCode: null,
    distanceMin: null,
    distanceMax: null,
    gainMin: null,
    gainMax: null,
    sort: "recent",
    order: "desc",
    page: 1,
    limit: 20,
};

export const useShopStore = create<ShopState>((set) => ({
    tab: "offers",
    bbox: null,

    offers: defaultOffers,
    routes: defaultRoutes,

    setTab: (tab) => set({ tab }),
    setBbox: (bbox) => set({ bbox }),

    setOffers: (patch) =>
        set((s) => ({
            offers: { ...s.offers, ...patch },
        })),

    resetOffersPage: () =>
        set((s) => ({
            offers: { ...s.offers, page: 1 },
        })),

    setRoutes: (patch) =>
        set((s) => ({
            routes: { ...s.routes, ...patch },
        })),

    resetRoutesPage: () =>
        set((s) => ({
            routes: { ...s.routes, page: 1 },
        })),

    resetAll: () =>
        set(() => ({
            tab: "offers",
            bbox: null,
            offers: defaultOffers,
            routes: defaultRoutes,
        })),
}));
