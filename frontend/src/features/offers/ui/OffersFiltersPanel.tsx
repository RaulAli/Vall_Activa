import type { OfferFiltersMeta } from "../domain/types";

export function OffersFiltersPanel({
    meta,
    q,
    discountType,
    inStock,
    onChangeQ,
    onToggleInStock,
    onChangeDiscountType,
}: {
    meta: OfferFiltersMeta;
    q: string;
    discountType: string | null;
    inStock: boolean;
    onChangeQ: (v: string) => void;
    onToggleInStock: () => void;
    onChangeDiscountType: (v: string | null) => void;
}) {
    return (
        <div style={{ borderBottom: "1px solid #eee", paddingBottom: 12, marginBottom: 12 }}>
            <div style={{ display: "flex", gap: 8 }}>
                <input
                    value={q}
                    onChange={(e) => onChangeQ(e.target.value)}
                    placeholder="Buscar..."
                    style={{ flex: 1, padding: 8, borderRadius: 8, border: "1px solid #ddd" }}
                />
                <button onClick={onToggleInStock} style={{ padding: "8px 10px" }}>
                    {inStock ? "Stock ✓" : "Stock"}
                </button>
            </div>

            <div style={{ marginTop: 10, fontSize: 12, opacity: 0.8 }}>
                Price: {meta.price.min ?? "—"} - {meta.price.max ?? "—"} | Points: {meta.points.min ?? "—"} -{" "}
                {meta.points.max ?? "—"} | Offers: {meta.counts.offers} | Businesses: {meta.counts.businesses}
            </div>

            <div style={{ marginTop: 10 }}>
                <div style={{ fontWeight: 600, marginBottom: 6 }}>Discount Type</div>
                <div style={{ display: "flex", gap: 8, flexWrap: "wrap" }}>
                    <button onClick={() => onChangeDiscountType(null)} style={{ padding: "6px 10px" }}>
                        All
                    </button>
                    {meta.discountTypes.map((d) => (
                        <button
                            key={d.value}
                            onClick={() => onChangeDiscountType(d.value)}
                            style={{
                                padding: "6px 10px",
                                border: discountType === d.value ? "2px solid #333" : "1px solid #ddd",
                                borderRadius: 8,
                            }}
                        >
                            {d.value} ({d.count})
                        </button>
                    ))}
                </div>
            </div>
        </div>
    );
}
