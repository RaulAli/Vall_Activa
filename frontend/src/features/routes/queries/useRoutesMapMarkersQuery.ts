import { useQuery } from "@tanstack/react-query";
import { endpoints } from "../../../shared/api/endpoints";
import { http } from "../../../shared/api/http";
import { bboxToParam, type Bbox } from "../../../shared/utils/bbox";
import { focusToParam, type Focus } from "../../../shared/utils/focus";
import { RouteMapMarkerSchema } from "../domain/schemas";
import type { RouteMapMarker } from "../domain/types";

export type RoutesMapMarkersParams = {
    bbox: Bbox | null;
    focus: Focus | null;

    q: string;
    sportCode: string | null;

    distanceMin: number | null;
    distanceMax: number | null;

    gainMin: number | null;
    gainMax: number | null;
};

export function useRoutesMapMarkersQuery(params: RoutesMapMarkersParams) {
    const hasGeo = params.focus !== null || params.bbox !== null;

    return useQuery({
        queryKey: ["routes", "map-markers", params],
        queryFn: async () => {
            const data = await http<unknown>("GET", endpoints.routes.mapMarkers, {
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

            return RouteMapMarkerSchema.array().parse(data.items) as RouteMapMarker[];
        },
        enabled: hasGeo,
    });
}
