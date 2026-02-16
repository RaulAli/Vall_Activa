import { useEffect, useRef } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import { useShopStore } from "../../store/shopStore";
import type { ShopTab } from "../../store/shopStore";

/**
 * Hook to synchronize the shop store state with URL query parameters.
 * It handles initialization from URL on mount and reactive updates to URL when state changes.
 */
export function useShopUrlSync() {
    const location = useLocation();
    const navigate = useNavigate();
    const isFirstRender = useRef(true);

    const {
        tab,
        offers,
        routes,
        setOffers,
        setRoutes,
        setTab
    } = useShopStore();

    // 0. Sync tab with pathname whenever location changes
    useEffect(() => {
        const currentTab: ShopTab = location.pathname.includes("offers") ? "offers" : "routes";
        if (tab !== currentTab) {
            setTab(currentTab);
        }
    }, [location.pathname, tab, setTab]);

    // 1. Initialize store from URL on mount
    useEffect(() => {
        if (!isFirstRender.current) return;
        isFirstRender.current = false;

        const params = new URLSearchParams(location.search);

        // Tab check based on pathname (already handled in ShopPage but good to keep consistent)
        const currentTab: ShopTab = location.pathname.includes("offers") ? "offers" : "routes";

        if (currentTab === "offers") {
            const patch: any = {};
            if (params.has("q")) patch.q = params.get("q");
            if (params.has("discountType")) patch.discountType = params.get("discountType");
            if (params.has("inStock")) patch.inStock = params.get("inStock") === "true";
            if (params.has("priceMin")) patch.priceMin = Number(params.get("priceMin"));
            if (params.has("priceMax")) patch.priceMax = Number(params.get("priceMax"));
            if (params.has("pointsMin")) patch.pointsMin = Number(params.get("pointsMin"));
            if (params.has("pointsMax")) patch.pointsMax = Number(params.get("pointsMax"));
            if (params.has("sort")) patch.sort = params.get("sort");
            if (params.has("order")) patch.order = params.get("order");
            if (params.has("page")) patch.page = Number(params.get("page"));

            if (Object.keys(patch).length > 0) {
                setOffers(patch);
            }
        } else {
            const patch: any = {};
            if (params.has("q")) patch.q = params.get("q");
            if (params.has("sportCode")) patch.sportCode = params.get("sportCode");
            if (params.has("distanceMin")) patch.distanceMin = Number(params.get("distanceMin"));
            if (params.has("distanceMax")) patch.distanceMax = Number(params.get("distanceMax"));
            if (params.has("gainMin")) patch.gainMin = Number(params.get("gainMin"));
            if (params.has("gainMax")) patch.gainMax = Number(params.get("gainMax"));
            if (params.has("sort")) patch.sort = params.get("sort");
            if (params.has("order")) patch.order = params.get("order");
            if (params.has("page")) patch.page = Number(params.get("page"));

            if (Object.keys(patch).length > 0) {
                setRoutes(patch);
            }
        }
    }, []);

    // 2. Update URL when state changes (debounced-ish via useEffect deps)
    useEffect(() => {
        // Skip on first load as it might overwrite URL if store isn't ready
        // But we want it to react to tab changes and filter changes

        const params = new URLSearchParams();
        const state = tab === "offers" ? offers : routes;

        // Add common filters
        if (state.q) params.set("q", state.q);
        if (state.sort && state.sort !== "recent") params.set("sort", state.sort);
        if (state.order && state.order !== "desc") params.set("order", state.order);
        if (state.page && state.page > 1) params.set("page", state.page.toString());

        if (tab === "offers") {
            const o = offers;
            if (o.discountType) params.set("discountType", o.discountType);
            if (o.inStock) params.set("inStock", "true");
            if (o.priceMin !== null) params.set("priceMin", o.priceMin.toString());
            if (o.priceMax !== null) params.set("priceMax", o.priceMax.toString());
            if (o.pointsMin !== null) params.set("pointsMin", o.pointsMin.toString());
            if (o.pointsMax !== null) params.set("pointsMax", o.pointsMax.toString());
        } else {
            const r = routes;
            if (r.sportCode) params.set("sportCode", r.sportCode);
            if (r.distanceMin !== null) params.set("distanceMin", r.distanceMin.toString());
            if (r.distanceMax !== null) params.set("distanceMax", r.distanceMax.toString());
            if (r.gainMin !== null) params.set("gainMin", r.gainMin.toString());
            if (r.gainMax !== null) params.set("gainMax", r.gainMax.toString());
        }

        const queryString = params.toString();
        const newSearch = queryString ? `?${queryString}` : "";

        if (location.search !== newSearch) {
            navigate({ pathname: location.pathname, search: newSearch }, { replace: true });
        }
    }, [tab, offers, routes, navigate, location.pathname, location.search]);
}
