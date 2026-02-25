import { useInfiniteQuery } from "@tanstack/react-query";
import { endpoints } from "../../../shared/api/endpoints";
import { http } from "../../../shared/api/http";
import { bboxToParam, type Bbox } from "../../../shared/utils/bbox";
import { PaginatedSchema, RouteListItemSchema } from "../domain/schemas";
import type { PaginatedResult, RouteListItem } from "../domain/types";

const LIMIT = 10;

export type RoutesListParams = {
    bbox: Bbox | null;
    focusBbox: Bbox | null;

    q: string;
    sportCode: string | null;

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
            const data = await http<unknown>("GET", endpoints.routes.list, {
                query: {
                    bbox: geoParam,
                    q: params.q || null,
                    sportCode: params.sportCode ?? null,
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
                },
            });

            return PaginatedSchema(RouteListItemSchema).parse(data) as PaginatedResult<RouteListItem>;
        },
        getNextPageParam: (lastPage) => {
            const loaded = lastPage.page * LIMIT;
            return loaded < lastPage.total ? lastPage.page + 1 : undefined;
        },
        enabled: params.enabled ?? !!geoParam,
        staleTime: 3_000,
    });
}
