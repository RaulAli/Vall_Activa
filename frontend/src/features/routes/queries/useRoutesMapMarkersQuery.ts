import { useQuery } from "@tanstack/react-query";
import { endpoints } from "../../../shared/api/endpoints";
import { http } from "../../../shared/api/http";
import { bboxToParam, type Bbox } from "../../../shared/utils/bbox";
import { z } from "zod";

export const RouteMapMarkerSchema = z.object({
    slug: z.string(),
    title: z.string(),
    lat: z.number(),
    lng: z.number(),
});

export const RouteMapMarkersResponseSchema = z.object({
    items: z.array(RouteMapMarkerSchema),
});

export type RouteMapMarker = z.infer<typeof RouteMapMarkerSchema>;

export type RoutesMapMarkersParams = {
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
};

export function useRoutesMapMarkersQuery(params: RoutesMapMarkersParams) {
    const geoParam = params.focusBbox ? bboxToParam(params.focusBbox) : bboxToParam(params.bbox);

    const key = [
        "routes",
        "map-markers",
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
    ] as const;

    return useQuery({
        queryKey: key,
        queryFn: async () => {
            const data = await http<unknown>("GET", endpoints.routes.mapMarkers, {
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
                },
            });

            return RouteMapMarkersResponseSchema.parse(data);
        },
        enabled: !!geoParam,
        placeholderData: (prev) => prev,
        staleTime: 3_000,
    });
}
