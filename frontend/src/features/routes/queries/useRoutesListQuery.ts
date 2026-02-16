import { useQuery } from "@tanstack/react-query";
import { endpoints } from "../../../shared/api/endpoints";
import { http } from "../../../shared/api/http";
import { bboxToParam, type Bbox } from "../../../shared/utils/bbox";
import { PaginatedSchema, RouteListItemSchema } from "../domain/schemas";
import type { PaginatedResult, RouteListItem } from "../domain/types";

export type RoutesListParams = {
    bbox: Bbox | null;
    focusBbox: Bbox | null;

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
    enabled?: boolean;
};

export function useRoutesListQuery(params: RoutesListParams) {
    const geoParam = params.focusBbox ? bboxToParam(params.focusBbox) : bboxToParam(params.bbox);

    // ⚠️ queryKey: NO metas el objeto params entero (cambia por referencia y dispara refetch)
    // Creamos una key estable con valores primitivos
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
        params.sort,
        params.order,
        params.page,
        params.limit,
    ] as const;

    return useQuery({
        queryKey: key,
        queryFn: async () => {
            const data = await http<unknown>("GET", endpoints.routes.list, {
                query: {
                    // 👇 prioridad: focusBbox si existe
                    bbox: geoParam,
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
        enabled: params.enabled ?? !!geoParam,
        placeholderData: (prev) => prev,
        staleTime: 3_000,
    });
}
