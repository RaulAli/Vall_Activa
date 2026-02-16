import { useEffect } from "react";
import { MapContainer, TileLayer, useMapEvents, useMap } from "react-leaflet";
import type { LeafletEvent, LatLngExpression } from "leaflet";
import { useShopStore } from "../../store/shopStore";
import { OffersMapPanel } from "./maps/OffersMapPanel";
import { RoutesMapPanel } from "./maps/RoutesMapPanel";

function BboxWatcher() {
    const setBbox = useShopStore((s) => s.setBbox);
    const tab = useShopStore((s) => s.tab);
    const routesFocusBbox = useShopStore((s) => s.routes.focusBbox);
    const map = useMap();

    // Set initial bbox on mount so data loads automatically
    useEffect(() => {
        const b = map.getBounds();
        setBbox({
            minLng: b.getWest(),
            minLat: b.getSouth(),
            maxLng: b.getEast(),
            maxLat: b.getNorth(),
        });
    }, [map, setBbox]);

    useMapEvents({
        moveend: (e: LeafletEvent) => {
            if (tab === "routes" && routesFocusBbox) return;

            const b = (e.target as any).getBounds();
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
    const spainCenter: LatLngExpression = [40.4168, -3.7038];

    return (
        <MapContainer
            center={spainCenter}
            zoom={6}
            style={{ height: "100%", width: "100%" }}
        >
            <TileLayer
                attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            />

            <BboxWatcher />

            {tab === "offers" ? <OffersMapPanel /> : <RoutesMapPanel />}
        </MapContainer>
    );
}
