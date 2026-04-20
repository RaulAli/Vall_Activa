import { useInfiniteQuery } from "@tanstack/react-query";
import { endpoints } from "../../../shared/api/endpoints";
import { bboxToParam, type Bbox } from "../../../shared/utils/bbox";
import { PaginatedSchema, RouteListItemSchema } from "../domain/schemas";
import type { PaginatedResult, RouteListItem } from "../domain/types";
import { env } from "../../../app/config/env";

const LIMIT = 10;

export type AppliedRouteFilters = {
    sportCode: string | null;
    guideOnly: boolean;
    distanceMin: number | null;
    distanceMax: number | null;
    gainMin: number | null;
    gainMax: number | null;
    difficulty: string | null;
    routeType: string | null;
    durationMin: number | null;
    durationMax: number | null;
    sort: "recent" | "distance" | "gain" | null;
    order: "asc" | "desc" | null;
};

export type RoutesListPage = PaginatedResult<RouteListItem> & {
    appliedFilters: AppliedRouteFilters;
};

export type RoutesListParams = {
    bbox: Bbox | null;
    focusBbox: Bbox | null;

    q: string;
    sportCode: string | null;
    guideOnly?: boolean;

    distanceMin: number | null;
    distanceMax: number | null;

    gainMin: number | null;
    gainMax: number | null;

    difficulty: string | null;
    routeType: string | null;

    durationMin: number | null;
    durationMax: number | null;

    sort: "recent" | "distance" | "gain";
    order: "asc" | "desc";

    enabled?: boolean;
};

export function useRoutesListQuery(params: RoutesListParams) {
    const geoParam = params.focusBbox ? bboxToParam(params.focusBbox) : bboxToParam(params.bbox);

    const key = [
        "routes",
        "list",
        geoParam,
        params.q || "",
        params.sportCode || "",
        params.guideOnly ? "1" : "0",
        params.distanceMin ?? "",
        params.distanceMax ?? "",
        params.gainMin ?? "",
        params.gainMax ?? "",
        params.difficulty ?? "",
        params.routeType ?? "",
        params.durationMin ?? "",
        params.durationMax ?? "",
        params.sort,
        params.order,
    ] as const;

    return useInfiniteQuery({
        queryKey: key,
        initialPageParam: 1,
        queryFn: async ({ pageParam }) => {
            const requestUrl = new URL(endpoints.routes.list, env.API_BASE_URL || window.location.origin);
            const queryParams: Record<string, string | number | null> = {
                bbox: geoParam,
                q: params.q || null,
                sportCode: params.sportCode ?? null,
                guideOnly: params.guideOnly ? 1 : null,
                distanceMin: params.distanceMin ?? null,
                distanceMax: params.distanceMax ?? null,
                gainMin: params.gainMin ?? null,
                gainMax: params.gainMax ?? null,
                difficulty: params.difficulty ?? null,
                routeType: params.routeType ?? null,
                durationMin: params.durationMin ?? null,
                durationMax: params.durationMax ?? null,
                sort: params.sort,
                order: params.order,
                page: pageParam,
                limit: LIMIT,
            };

            for (const [keyName, value] of Object.entries(queryParams)) {
                if (value === null || value === "") continue;
                requestUrl.searchParams.set(keyName, String(value));
            }

            const response = await fetch(requestUrl.toString(), {
                method: "GET",
                headers: { "Content-Type": "application/json" },
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            const parsed = PaginatedSchema(RouteListItemSchema).parse(data) as PaginatedResult<RouteListItem>;

            return {
                ...parsed,
                appliedFilters: extractAppliedFilters(response.headers),
            } as RoutesListPage;
        },
        getNextPageParam: (lastPage) => {
            const loaded = lastPage.page * LIMIT;
            return loaded < lastPage.total ? lastPage.page + 1 : undefined;
        },
        enabled: params.enabled ?? !!geoParam,
        staleTime: 3_000,
    });
}

function extractAppliedFilters(headers: Headers): AppliedRouteFilters {
    return {
        sportCode: normalizeString(headers.get("X-Search-Applied-Sport-Code")),
        guideOnly: normalizeBool(headers.get("X-Search-Applied-Guide-Only")),
        distanceMin: normalizeInt(headers.get("X-Search-Applied-Distance-Min")),
        distanceMax: normalizeInt(headers.get("X-Search-Applied-Distance-Max")),
        gainMin: normalizeInt(headers.get("X-Search-Applied-Gain-Min")),
        gainMax: normalizeInt(headers.get("X-Search-Applied-Gain-Max")),
        difficulty: normalizeString(headers.get("X-Search-Applied-Difficulty")),
        routeType: normalizeString(headers.get("X-Search-Applied-Route-Type")),
        durationMin: normalizeInt(headers.get("X-Search-Applied-Duration-Min")),
        durationMax: normalizeInt(headers.get("X-Search-Applied-Duration-Max")),
        sort: normalizeSort(headers.get("X-Search-Applied-Sort")),
        order: normalizeOrder(headers.get("X-Search-Applied-Order")),
    };
}

function normalizeBool(value: string | null): boolean {
    if (!value) return false;
    const normalized = value.trim().toLowerCase();
    return normalized === "1" || normalized === "true" || normalized === "yes" || normalized === "on";
}

function normalizeString(value: string | null): string | null {
    if (!value) return null;
    const trimmed = value.trim();
    return trimmed === "" ? null : trimmed;
}

function normalizeInt(value: string | null): number | null {
    if (!value) return null;
    const parsed = Number.parseInt(value, 10);
    return Number.isFinite(parsed) ? parsed : null;
}

function normalizeSort(value: string | null): AppliedRouteFilters["sort"] {
    if (!value) return null;
    const normalized = value.toLowerCase();
    if (normalized === "recent" || normalized === "distance" || normalized === "gain") {
        return normalized;
    }
    return null;
}

function normalizeOrder(value: string | null): AppliedRouteFilters["order"] {
    if (!value) return null;
    const normalized = value.toLowerCase();
    if (normalized === "asc" || normalized === "desc") {
        return normalized;
    }
    return null;
}
