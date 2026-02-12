import { useQuery } from "@tanstack/react-query";
import { endpoints } from "../../../shared/api/endpoints";
import { http } from "../../../shared/api/http";
import { bboxToParam, type Bbox } from "../../../shared/utils/bbox";
import { OfferFiltersMetaSchema } from "../domain/schemas";
import type { OfferFiltersMeta } from "../domain/types";

export type OffersFiltersParams = {
    bbox: Bbox | null;
    q: string;
    discountType: string | null; // puede influir ranges (v2)
    inStock: boolean;
};

export function useOffersFiltersQuery(params: OffersFiltersParams) {
    return useQuery({
        queryKey: ["offers", "filters", params],
        queryFn: async () => {
            const data = await http<unknown>("GET", endpoints.offers.filters, {
                query: {
                    bbox: bboxToParam(params.bbox),
                    q: params.q || null,
                    discountType: params.discountType ?? null,
                    inStock: params.inStock ? "1" : null,
                },
            });
            return OfferFiltersMetaSchema.parse(data) as OfferFiltersMeta;
        },
        enabled: params.bbox !== null,
    });
}
