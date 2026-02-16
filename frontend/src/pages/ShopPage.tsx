import { ShopLayout } from "../widgets/shop/ShopLayout";
import { ShopSidebar } from "../widgets/shop/ShopSidebar";
import { ShopMap } from "../widgets/shop/ShopMap";
import { useShopUrlSync } from "../shared/hooks/useShopUrlSync";

export function ShopPage() {
    useShopUrlSync();

    return (
        <ShopLayout
            sidebar={<ShopSidebar />}
            map={<ShopMap />}
        />
    );
}
