import { Polyline, Popup } from "react-leaflet";
import polyline from "@mapbox/polyline";

type Props = {
    slug: string;
    title: string;
    encoded: string;
};

export function SelectedRoutePolylineLayer({ slug, title, encoded }: Props) {
    const latlngs = polyline.decode(encoded).map(([lat, lng]) => [lat, lng] as [number, number]);

    if (latlngs.length < 2) return null;

    return (
        <Polyline positions={latlngs}>
            <Popup>
                <div style={{ fontWeight: 700 }}>{title}</div>
                <div style={{ opacity: 0.8, fontSize: 12 }}>{slug}</div>
            </Popup>
        </Polyline>
    );
}
