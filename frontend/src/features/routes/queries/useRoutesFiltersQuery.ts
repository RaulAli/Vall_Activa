import { useQuery } from "@tanstack/react-query";
import { endpoints } from "../../../shared/api/endpoints";
import { http } from "../../../shared/api/http";
import { bboxToParam, type Bbox } from "../../../shared/utils/bbox";
import { focusToParam, type Focus } from "../../../shared/utils/focus";
import { RouteFiltersMetaSchema } from "../domain/schemas";
import type { RouteFiltersMeta } from "../domain/types";

export type RoutesFiltersParams = {
    bbox: Bbox | null;
    focus: Focus | null;

    q: string;
    sportCode: string | null;

    distanceMin: number | null;
    distanceMax: number | null;

    gainMin: number | null;
    gainMax: number | null;
};

export function useRoutesFiltersQuery(params: RoutesFiltersParams) {
    const hasGeo = params.focus !== null || params.bbox !== null;

    return useQuery({
        queryKey: ["routes", "filters", params],
        queryFn: async () => {
            const data = await http<unknown>("GET", endpoints.routes.filters, {
                query: {
                    focus: focusToParam(params.focus),
                    bbox: params.focus ? null : bboxToParam(params.bbox),

                    q: params.q || null,
                    sportCode: params.sportCode ?? null,
                    distanceMin: params.distanceMin ?? null,
                    distanceMax: params.distanceMax ?? null,
                    gainMin: params.gainMin ?? null,
                    gainMax: params.gainMax ?? null,
                },
            });

            return RouteFiltersMetaSchema.parse(data) as RouteFiltersMeta;
        },
        enabled: hasGeo,
    });
}
