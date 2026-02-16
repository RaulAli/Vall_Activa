import { create } from "zustand";
import type { Bbox } from "../shared/utils/bbox";
import type { Focus } from "../shared/utils/focus";

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

    // ✅ NUEVO: modo foco (cluster click)
    focus: Focus | null;

    // ✅ NUEVO: (opcional) estado UI para mostrar una polyline seleccionada
    // Nota: con tu backend v2 list NO devuelve polyline; esto quedará útil cuando lo pintes con /details
    selected: { slug: string } | null;
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

    // ✅ NUEVO: helpers focus/selected
    setRoutesFocus: (focus: Focus | null) => void;
    clearRoutesFocus: () => void;

    setRoutesSelected: (sel: { slug: string } | null) => void;
    clearRoutesSelected: () => void;

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

    focus: null,
    selected: null,
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

    setRoutesFocus: (focus) =>
        set((s) => ({
            routes: {
                ...s.routes,
                focus,
                page: 1, // cuando cambias foco reinicias paginación
                selected: null, // limpias selección
            },
        })),

    clearRoutesFocus: () =>
        set((s) => ({
            routes: {
                ...s.routes,
                focus: null,
                page: 1,
                selected: null,
            },
        })),

    setRoutesSelected: (sel) =>
        set((s) => ({
            routes: {
                ...s.routes,
                selected: sel,
            },
        })),

    clearRoutesSelected: () =>
        set((s) => ({
            routes: {
                ...s.routes,
                selected: null,
            },
        })),

    resetAll: () =>
        set(() => ({
            tab: "offers",
            bbox: null,
            offers: defaultOffers,
            routes: defaultRoutes,
        })),
}));
