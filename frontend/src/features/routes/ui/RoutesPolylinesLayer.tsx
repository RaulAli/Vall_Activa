import { Polyline } from "react-leaflet";
import type { RouteListItem } from "../domain/types";
import { decodePolyline } from "./decodePolyline";

export function RoutesPolylinesLayer({ items }: { items: RouteListItem[] }) {
    return (
        <>
            {items
                .filter((r) => r.polyline)
                .map((r) => {
                    const points = decodePolyline(r.polyline!);
                    if (points.length < 2) return null;
                    return <Polyline key={r.id} positions={points} />;
                })}
        </>
    );
}
