import { z } from "zod";

export const OfferListItemSchema = z.object({
    id: z.string(),
    businessId: z.string(),
    title: z.string(),
    slug: z.string(),
    description: z.string().nullable().optional().default(null),
    price: z.string(),
    currency: z.string(),
    image: z.string().nullable().optional().default(null),
    discountType: z.string(),
    status: z.string(),
    quantity: z.number(),
    pointsCost: z.number(),
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

export const OfferFiltersMetaSchema = z.object({
    price: z.object({ min: z.string().nullable(), max: z.string().nullable() }),
    points: z.object({ min: z.number().nullable(), max: z.number().nullable() }),
    discountTypes: z.array(z.object({ value: z.string(), count: z.number() })),
    counts: z.object({ offers: z.number(), businesses: z.number() }),
    bounds: z
        .object({
            minLng: z.number(),
            minLat: z.number(),
            maxLng: z.number(),
            maxLat: z.number(),
        })
        .nullable(),
});

export const BusinessMapMarkerSchema = z.object({
    businessUserId: z.string(),
    slug: z.string(),
    name: z.string(),
    lat: z.number().nullable(),
    lng: z.number().nullable(),
    profileIcon: z.string().nullable(),
    offersCount: z.number(),
});
