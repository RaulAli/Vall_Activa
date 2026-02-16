import { useQuery } from "@tanstack/react-query";
import { endpoints } from "../../../shared/api/endpoints";
import { http } from "../../../shared/api/http";
import { z } from "zod";

export const RouteDetailsSchema = z.object({
    id: z.string(),
    sportId: z.string(),
    title: z.string(),
    slug: z.string(),
    description: z.string().nullable(),

    visibility: z.string(),
    status: z.string(),

    startLat: z.number().nullable(),
    startLng: z.number().nullable(),
    endLat: z.number().nullable(),
    endLng: z.number().nullable(),

    minLat: z.number().nullable(),
    minLng: z.number().nullable(),
    maxLat: z.number().nullable(),
    maxLng: z.number().nullable(),

    distanceM: z.number(),
    elevationGainM: z.number(),
    elevationLossM: z.number(),

    polyline: z.string().nullable(),
    createdAt: z.string(),
});

export type RouteDetails = z.infer<typeof RouteDetailsSchema>;

export function useRouteDetailsQuery(slug: string | null) {
    return useQuery({
        queryKey: ["routes", "details", slug],
        queryFn: async () => {
            const data = await http<unknown>("GET", endpoints.routes.details(slug as string));
            return RouteDetailsSchema.parse(data) as RouteDetails;
        },
        enabled: !!slug,
        staleTime: 30_000,
    });
}
