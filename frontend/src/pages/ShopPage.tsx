import { ShopLayout } from "../widgets/shop/ShopLayout";
import { ShopSidebar } from "../widgets/shop/ShopSidebar";
import { ShopMap } from "../widgets/shop/ShopMap";
import { useShopStore } from "../store/shopStore";

export function ShopPage() {
    const tab = useShopStore((s) => s.tab);
    const setTab = useShopStore((s) => s.setTab);

    return (
        <ShopLayout
            sidebar={
                <div>
                    <div style={{ display: "flex", gap: 8, marginBottom: 12 }}>
                        <button
                            onClick={() => setTab("offers")}
                            style={{ padding: "8px 10px", border: tab === "offers" ? "2px solid #333" : "1px solid #ddd" }}
                        >
                            Offers
                        </button>
                        <button
                            onClick={() => setTab("routes")}
                            style={{ padding: "8px 10px", border: tab === "routes" ? "2px solid #333" : "1px solid #ddd" }}
                        >
                            Routes
                        </button>
                    </div>

                    <ShopSidebar />
                </div>
            }
            map={<ShopMap />}
        />
    );
}
