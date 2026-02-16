import { useEffect } from "react";
import { MapContainer, TileLayer, Marker, Polyline, useMap } from "react-leaflet";
import L from "leaflet";
import polyline from "@mapbox/polyline";

// Fix Leaflet marker icon issue
import markerIcon from "leaflet/dist/images/marker-icon.png";
import markerIconRetina from "leaflet/dist/images/marker-icon-2x.png";
import markerShadow from "leaflet/dist/images/marker-shadow.png";

const DefaultIcon = L.icon({
    iconUrl: markerIcon,
    iconRetinaUrl: markerIconRetina,
    shadowUrl: markerShadow,
    iconSize: [25, 41],
    iconAnchor: [12, 41],
});

L.Marker.prototype.options.icon = DefaultIcon;

type Props = {
    center?: [number, number];
    zoom?: number;
    polylineEncoded?: string | null;
    marker?: {
        lat: number;
        lng: number;
        title?: string;
    } | null;
};

function MapAutoControl({ polylineEncoded, marker }: { polylineEncoded?: string | null, marker?: { lat: number; lng: number } | null }) {
    const map = useMap();

    useEffect(() => {
        if (polylineEncoded) {
            const latlngs = polyline.decode(polylineEncoded).map(([lat, lng]) => [lat, lng] as [number, number]);
            if (latlngs.length >= 2) {
                const bounds = L.latLngBounds(latlngs);
                map.fitBounds(bounds, { padding: [50, 50], animate: true });
            }
        } else if (marker) {
            map.setView([marker.lat, marker.lng], 15, { animate: true });
        }
    }, [map, polylineEncoded, marker]);

    return null;
}

export function DetailsMap({ center = [40.4168, -3.7038], zoom = 13, polylineEncoded, marker }: Props) {
    const latlngs = polylineEncoded ? polyline.decode(polylineEncoded).map(([lat, lng]) => [lat, lng] as [number, number]) : [];

    return (
        <MapContainer
            center={center}
            zoom={zoom}
            style={{ height: "100%", width: "100%" }}
            scrollWheelZoom={false}
        >
            <TileLayer
                attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            />

            <MapAutoControl polylineEncoded={polylineEncoded} marker={marker} />

            {polylineEncoded && latlngs.length >= 2 && (
                <Polyline positions={latlngs} color="var(--color-primary, #3b82f6)" weight={5} />
            )}

            {marker && (
                <Marker position={[marker.lat, marker.lng]} />
            )}
        </MapContainer>
    );
}
