import { useShopStore } from "../../store/shopStore";
import { OffersSidebarPanel } from "./sidebars/OffersSidebarPanel";
import { RoutesSidebarPanel } from "./sidebars/RoutesSidebarPanel";

export function ShopSidebar() {
    const tab = useShopStore((s) => s.tab);

    return (
        <div>
            {tab === "offers" ? <OffersSidebarPanel /> : <RoutesSidebarPanel />}
        </div>
    );
}
