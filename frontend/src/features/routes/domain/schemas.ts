import { z } from "zod";

export const RouteListItemSchema = z.object({
    id: z.string(),
    sportId: z.string(),
    title: z.string(),
    slug: z.string(),

    // list v2
    startLat: z.number().nullable(),
    startLng: z.number().nullable(),

    distanceM: z.number(),
    elevationGainM: z.number(),
    elevationLossM: z.number(),

    isActive: z.boolean(),
    createdAt: z.string(),
    image: z.string().nullable(),
});

export const PaginatedSchema = <T extends z.ZodTypeAny>(item: T) =>
    z.object({
        page: z.number(),
        limit: z.number(),
        total: z.number(),
        items: z.array(item),
    });

export const RouteFiltersMetaSchema = z.object({
    distance: z.object({ min: z.number().nullable(), max: z.number().nullable() }),
    gain: z.object({ min: z.number().nullable(), max: z.number().nullable() }),
    sports: z.array(z.object({ code: z.string(), name: z.string(), count: z.number() })),
    counts: z.object({ routes: z.number() }),
    bounds: z
        .object({
            minLng: z.number(),
            minLat: z.number(),
            maxLng: z.number(),
            maxLat: z.number(),
        })
        .nullable(),
});


export const RouteMapMarkerSchema = z.object({
    slug: z.string(),
    title: z.string(),
    lat: z.number(),
    lng: z.number(),
});

export const RouteMapMarkersResponseSchema = z.object({
    items: z.array(RouteMapMarkerSchema),
});

