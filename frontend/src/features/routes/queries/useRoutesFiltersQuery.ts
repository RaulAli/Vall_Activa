import { useQuery } from "@tanstack/react-query";
import { endpoints } from "../../../shared/api/endpoints";
import { http } from "../../../shared/api/http";
import { bboxToParam, type Bbox } from "../../../shared/utils/bbox";
import { RouteFiltersMetaSchema } from "../domain/schemas";
import type { RouteFiltersMeta } from "../domain/types";
import { type FocusBbox } from "../../../shared/utils/focus";

export type RoutesFiltersParams = {
    bbox: Bbox | null;
    focusBbox: FocusBbox | null;

    q: string;
    sportCode: string | null;

    distanceMin: number | null;
    distanceMax: number | null;

    gainMin: number | null;
    gainMax: number | null;
};

export function useRoutesFiltersQuery(params: RoutesFiltersParams) {
    const hasGeo = params.focusBbox !== null || params.bbox !== null;

    return useQuery({
        queryKey: ["routes", "filters", params],
        queryFn: async () => {
            const data = await http<unknown>("GET", endpoints.routes.filters, {
                query: {
                    bbox: params.focusBbox ? bboxToParam(params.focusBbox) : bboxToParam(params.bbox),

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
        placeholderData: (prev) => prev,
        enabled: hasGeo,
    });
}
