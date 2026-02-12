import { useQuery } from "@tanstack/react-query";
import { endpoints } from "../../../shared/api/endpoints";
import { http } from "../../../shared/api/http";
import { bboxToParam, type Bbox } from "../../../shared/utils/bbox";
import { PaginatedSchema, RouteListItemSchema } from "../domain/schemas";
import type { PaginatedResult, RouteListItem } from "../domain/types";

export type RoutesListParams = {
    bbox: Bbox | null;

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

export function useRoutesListQuery(params: RoutesListParams) {
    return useQuery({
        queryKey: ["routes", "list", params],
        queryFn: async () => {
            const data = await http<unknown>("GET", endpoints.routes.list, {
                query: {
                    bbox: bboxToParam(params.bbox),
                    q: params.q || null,
                    sportCode: params.sportCode ?? null,
                    distanceMin: params.distanceMin ?? null,
                    distanceMax: params.distanceMax ?? null,
                    gainMin: params.gainMin ?? null,
                    gainMax: params.gainMax ?? null,
                    sort: params.sort,
                    order: params.order,
                    page: params.page,
                    limit: params.limit,
                },
            });

            return PaginatedSchema(RouteListItemSchema).parse(data) as PaginatedResult<RouteListItem>;
        },
        enabled: params.bbox !== null,
    });
}
