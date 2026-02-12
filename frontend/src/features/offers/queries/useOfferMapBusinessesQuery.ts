import { useQuery } from "@tanstack/react-query";
import { endpoints } from "../../../shared/api/endpoints";
import { http } from "../../../shared/api/http";
import { bboxToParam, type Bbox } from "../../../shared/utils/bbox";
import { BusinessMapMarkerSchema } from "../domain/schemas";
import type { BusinessMapMarker } from "../domain/types";
import { z } from "zod";

const BusinessMarkerListSchema = z.object({
    items: z.array(BusinessMapMarkerSchema),
});

export type OfferMapBusinessesParams = {
    bbox: Bbox | null;
    q: string;
    discountType: string | null;
    inStock: boolean;
};

export function useOfferMapBusinessesQuery(params: OfferMapBusinessesParams) {
    return useQuery({
        queryKey: ["offers", "map-businesses", params],
        queryFn: async () => {
            const data = await http<unknown>("GET", endpoints.offers.mapBusinesses, {
                query: {
                    bbox: bboxToParam(params.bbox),
                    q: params.q || null,
                    discountType: params.discountType ?? null,
                    inStock: params.inStock ? "1" : null,
                },
            });
            return BusinessMarkerListSchema.parse(data).items as BusinessMapMarker[];
        },
        enabled: params.bbox !== null,
    });
}
