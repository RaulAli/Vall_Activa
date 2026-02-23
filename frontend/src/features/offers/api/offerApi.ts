import { http } from "../../../shared/api/http";
import { endpoints } from "../../../shared/api/endpoints";
import type { MyOfferItem, CreateOfferPayload } from "../domain/types";

export async function getMyOffers(token: string): Promise<MyOfferItem[]> {
    return http<MyOfferItem[]>("GET", endpoints.offers.mine, {
        headers: { Authorization: `Bearer ${token}` },
    });
}

export async function createOffer(token: string, payload: CreateOfferPayload): Promise<string> {
    const data = await http<{ id: string }>("POST", endpoints.offers.mine, {
        headers: { Authorization: `Bearer ${token}` },
        body: payload,
    });
    return data.id;
}

export async function updateOffer(
    token: string,
    id: string,
    patch: Partial<Pick<MyOfferItem, "status" | "isActive" | "title" | "description" | "price" | "quantity" | "image" | "pointsCost" | "discountType">>
): Promise<void> {
    await http<unknown>("PATCH", endpoints.offers.update(id), {
        headers: { Authorization: `Bearer ${token}` },
        body: patch,
    });
}
