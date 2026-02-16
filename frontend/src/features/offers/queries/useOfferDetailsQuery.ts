import { useQuery } from "@tanstack/react-query";
import { endpoints } from "../../../shared/api/endpoints";
import { http } from "../../../shared/api/http";
import { z } from "zod";

export const OfferDetailsSchema = z.object({
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
    lat: z.number().nullable().optional(),
    lng: z.number().nullable().optional(),
    // Extended business info might be here in real API
    business: z.object({
        name: z.string(),
        slug: z.string(),
        profileIcon: z.string().nullable().optional(),
    }).optional(),
});

export type OfferDetails = z.infer<typeof OfferDetailsSchema>;

export function useOfferDetailsQuery(slug: string | null) {
    return useQuery({
        queryKey: ["offers", "details", slug],
        queryFn: async () => {
            const data = await http<unknown>("GET", endpoints.offers.details(slug as string));
            return OfferDetailsSchema.parse(data) as OfferDetails;
        },
        enabled: !!slug,
        staleTime: 30_000,
    });
}
