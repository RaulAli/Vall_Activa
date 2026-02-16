import type { RouteListItem } from "../domain/types";

export function RoutesList({
    items,
    onSelectRoute,
}: {
    items: RouteListItem[];
    onSelectRoute?: (r: RouteListItem) => void;
}) {
    return (
        <div style={{ display: "grid", gap: 10 }}>
            {items.map((r) => (
                <div
                    key={r.id}
                    style={{ border: "1px solid #eee", borderRadius: 10, padding: 10 }}
                >
                    <div style={{ fontWeight: 700 }}>{r.title}</div>
                    <div style={{ opacity: 0.8 }}>{r.distanceM}m · +{r.elevationGainM}m</div>

                    {onSelectRoute && (
                        <button style={{ marginTop: 8 }} onClick={() => onSelectRoute(r)}>
                            Ver ruta (polyline)
                        </button>
                    )}
                </div>
            ))}
        </div>
    );
}
