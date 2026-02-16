import MarkerClusterGroup from "react-leaflet-cluster";
import { Marker, Popup } from "react-leaflet";
import type { RouteMapMarker } from "../domain/types";
import { useShopStore } from "../../../store/shopStore";

export function RoutesMarkersLayer({ items }: { items: RouteMapMarker[] }) {
    const setRoutesFocus = useShopStore((s) => s.setRoutesFocus);

    return (
        <MarkerClusterGroup
            chunkedLoading
            eventHandlers={{
                clusterclick: (e: any) => {
                    const ll = e.layer?.getLatLng?.();
                    if (!ll) return;
                    setRoutesFocus({ lng: ll.lng, lat: ll.lat, radius: 100 });
                },
            }}
        >
            {items.map((m) => (
                <Marker
                    key={m.slug}
                    position={[m.lat, m.lng]}
                    eventHandlers={{
                        click: () => setRoutesFocus({ lng: m.lng, lat: m.lat, radius: 100 }),
                    }}
                >
                    <Popup>
                        <div style={{ fontWeight: 600 }}>{m.title}</div>
                        <div style={{ opacity: 0.8 }}>{m.slug}</div>
                        <div style={{ marginTop: 8, fontSize: 12, opacity: 0.8 }}>
                            Click para modo foco (100m)
                        </div>
                    </Popup>
                </Marker>
            ))}
        </MarkerClusterGroup>
    );
}
