import { MapContainer, TileLayer, useMapEvents } from "react-leaflet";
import { useShopStore } from "../../store/shopStore";
import { OffersMapPanel } from "./maps/OffersMapPanel";
import { RoutesMapPanel } from "./maps/RoutesMapPanel";

function BboxWatcher() {
    const setBbox = useShopStore((s) => s.setBbox);
    const tab = useShopStore((s) => s.tab);
    const routesFocus = useShopStore((s) => s.routes.focus);

    useMapEvents({
        moveend: (e) => {

            if (tab === "routes" && routesFocus) return;

            const b = e.target.getBounds();
            setBbox({
                minLng: b.getWest(),
                minLat: b.getSouth(),
                maxLng: b.getEast(),
                maxLat: b.getNorth(),
            });
        },
    });

    return null;
}

export function ShopMap() {
    const tab = useShopStore((s) => s.tab);

    return (
        <MapContainer center={[38.968, -0.181]} zoom={12} style={{ height: "100vh", width: "100%" }}>
            <TileLayer attribution="&copy; OpenStreetMap" url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />

            <BboxWatcher />

            {tab === "offers" ? <OffersMapPanel /> : <RoutesMapPanel />}
        </MapContainer>
    );
}
