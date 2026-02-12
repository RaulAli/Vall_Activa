import type { OfferListItem } from "../domain/types";

export function OfferCard({ item }: { item: OfferListItem }) {
    return (
        <div style={{ border: "1px solid #ddd", borderRadius: 10, padding: 12, marginBottom: 10 }}>
            <div style={{ fontWeight: 700 }}>{item.title}</div>
            <div style={{ opacity: 0.8 }}>{item.description ?? "—"}</div>
            <div style={{ marginTop: 8 }}>
                {item.price} {item.currency} · {item.discountType} · pts {item.pointsCost}
            </div>
        </div>
    );
}
