import { z } from "zod";

export const RouteListItemSchema = z.object({
    id: z.string(),
    sportId: z.string(),
    title: z.string(),
    slug: z.string(),
    polyline: z.string().nullable(),

    minLat: z.number().nullable(),
    minLng: z.number().nullable(),
    maxLat: z.number().nullable(),
    maxLng: z.number().nullable(),

    distanceM: z.number(),
    elevationGainM: z.number(),
    elevationLossM: z.number(),

    isActive: z.boolean(),
    createdAt: z.string(),
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
