import { Marker, Popup } from "react-leaflet";
import type { BusinessMapMarker } from "../domain/types";
import L from "leaflet";

// MVP: marcador simple; más adelante icono con profileIcon
const defaultIcon = new L.Icon({
    iconUrl: "https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png",
    iconRetinaUrl: "https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png",
    shadowUrl: "https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png",
    iconSize: [25, 41],
    iconAnchor: [12, 41],
});

export function BusinessMarkersLayer({ items }: { items: BusinessMapMarker[] }) {
    return (
        <>
            {items
                .filter((b) => b.lat !== null && b.lng !== null)
                .map((b) => (
                    <Marker key={b.businessUserId} position={[b.lat!, b.lng!]} icon={defaultIcon}>
                        <Popup>
                            <div style={{ fontWeight: 700 }}>{b.name}</div>
                            <div>Offers: {b.offersCount}</div>
                            <div style={{ opacity: 0.8 }}>{b.slug}</div>
                        </Popup>
                    </Marker>
                ))}
        </>
    );
}
