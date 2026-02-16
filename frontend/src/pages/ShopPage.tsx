import { useEffect } from "react";
import { useLocation } from "react-router-dom";
import { useShopStore } from "../store/shopStore";
import { ShopLayout } from "../widgets/shop/ShopLayout";
import { ShopSidebar } from "../widgets/shop/ShopSidebar";
import { ShopMap } from "../widgets/shop/ShopMap";

export function ShopPage() {
    const location = useLocation();
    const setTab = useShopStore((s) => s.setTab);

    useEffect(() => {
        if (location.pathname === "/offers") {
            setTab("offers");
        } else if (location.pathname === "/routes") {
            setTab("routes");
        }
    }, [location.pathname, setTab]);

    return (
        <ShopLayout
            sidebar={<ShopSidebar />}
            map={<ShopMap />}
        />
    );
}
