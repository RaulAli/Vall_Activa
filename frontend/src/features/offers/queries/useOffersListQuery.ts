import { useQuery } from "@tanstack/react-query";
import { endpoints } from "../../../shared/api/endpoints";
import { http } from "../../../shared/api/http";
import { bboxToParam, type Bbox } from "../../../shared/utils/bbox";
import { OfferListItemSchema, PaginatedSchema } from "../domain/schemas";
import type { OfferListItem, PaginatedResult } from "../domain/types";

export type OffersListParams = {
    bbox: Bbox | null;
    q: string;
    discountType: string | null;
    inStock: boolean;
    priceMin: number | null;
    priceMax: number | null;
    pointsMin: number | null;
    pointsMax: number | null;
    sort: string;
    order: string;
    page: number;
    limit: number;
    enabled?: boolean;
};

export function useOffersListQuery(params: OffersListParams) {
    return useQuery({
        queryKey: ["offers", "list", params],
        queryFn: async () => {
            const data = await http<unknown>("GET", endpoints.offers.list, {
                query: {
                    bbox: bboxToParam(params.bbox),
                    q: params.q || null,
                    discountType: params.discountType ?? null,
                    inStock: params.inStock ? "1" : null,
                    priceMin: params.priceMin ?? null,
                    priceMax: params.priceMax ?? null,
                    pointsMin: params.pointsMin ?? null,
                    pointsMax: params.pointsMax ?? null,
                    sort: params.sort,
                    order: params.order,
                    page: params.page,
                    limit: params.limit,
                },
            });

            const parsed = PaginatedSchema(OfferListItemSchema).parse(data);
            return parsed as PaginatedResult<OfferListItem>;
        },
        enabled: params.enabled ?? (params.bbox !== null),
    });
}
