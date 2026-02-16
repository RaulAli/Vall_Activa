import MarkerClusterGroup from "react-leaflet-cluster";
import { Marker, Popup } from "react-leaflet";
import type { RouteMapMarker } from "../domain/types";
import { useShopStore } from "../../../store/shopStore";

export function RoutesMarkersLayer({ items }: { items: RouteMapMarker[] }) {
    const setRoutesFocusBbox = useShopStore((s) => s.setRoutesFocusBbox);

    return (
        <MarkerClusterGroup
            chunkedLoading
            eventHandlers={{
                clusterclick: (e: any) => {
                    const bounds = e.layer.getBounds();
                    setRoutesFocusBbox({
                        minLng: bounds.getWest(),
                        minLat: bounds.getSouth(),
                        maxLng: bounds.getEast(),
                        maxLat: bounds.getNorth(),
                    });
                },
            }}
        >
            {items.map((m) => (
                <Marker
                    key={m.slug}
                    position={[m.lat, m.lng]}
                    eventHandlers={{
                        click: () =>
                            setRoutesFocusBbox({
                                minLng: m.lng - 0.005,
                                minLat: m.lat - 0.005,
                                maxLng: m.lng + 0.005,
                                maxLat: m.lat + 0.005,
                            }),
                    }}
                >
                    <Popup>
                        <div style={{ fontWeight: 600 }}>{m.title}</div>
                        <div style={{ opacity: 0.8 }}>{m.slug}</div>
                        <div style={{ marginTop: 8, fontSize: 12, opacity: 0.8 }}>
                            Click para modo foco
                        </div>
                    </Popup>
                </Marker>
            ))}
        </MarkerClusterGroup>
    );
}
