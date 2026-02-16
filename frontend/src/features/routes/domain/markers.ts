import { z } from "zod";

export const RouteMapMarkerSchema = z.object({
    slug: z.string(),
    title: z.string(),
    lat: z.number(),
    lng: z.number(),
});

export type RouteMapMarker = z.infer<typeof RouteMapMarkerSchema>;

export const RouteMapMarkersResponseSchema = z.object({
    items: z.array(RouteMapMarkerSchema),
});

export type RouteMapMarkersResponse = z.infer<typeof RouteMapMarkersResponseSchema>;
