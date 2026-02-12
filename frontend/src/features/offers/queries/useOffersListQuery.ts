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
    sort: string;
    order: string;
    page: number;
    limit: number;
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
                    sort: params.sort,
                    order: params.order,
                    page: params.page,
                    limit: params.limit,
                },
            });

            const parsed = PaginatedSchema(OfferListItemSchema).parse(data);
            return parsed as PaginatedResult<OfferListItem>;
        },
        enabled: params.bbox !== null, // MVP: solo cargar si ya hay bbox
    });
}
