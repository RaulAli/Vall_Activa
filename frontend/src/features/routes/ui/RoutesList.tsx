import { useShopStore } from "../../../store/shopStore";
import type { RouteListItem } from "../domain/types";

type Props = {
    items: RouteListItem[];
};

export function RoutesList({ items }: Props) {
    const routes = useShopStore((s) => s.routes);
    const setRoutesSelected = useShopStore((s) => s.setRoutesSelected);
    const clearRoutesFocusBbox = useShopStore((s) => s.clearRoutesFocusBbox);

    return (
        <div style={{ display: "grid", gap: 10 }}>
            {routes.focusBbox && (
                <div
                    style={{
                        padding: "10px 15px",
                        background: "#f8f9fa",
                        borderRadius: 10,
                        border: "1px solid #e9ecef",
                        display: "flex",
                        justifyContent: "space-between",
                        alignItems: "center",
                    }}
                >
                    <div style={{ fontSize: 13, color: "#666" }}>
                        📌 Modo foco activo ({items.length} rutas)
                    </div>
                    <button
                        onClick={() => clearRoutesFocusBbox()}
                        style={{
                            padding: "6px 12px",
                            background: "#dc3545",
                            color: "white",
                            border: "none",
                            borderRadius: 6,
                            cursor: "pointer",
                            fontSize: 12,
                            fontWeight: 600,
                        }}
                    >
                        Ver todas
                    </button>
                </div>
            )}
            {items.map((r) => {
                const isSelected = routes.selected?.slug === r.slug;

                return (
                    <div
                        key={r.id}
                        style={{
                            border: "1px solid #eee",
                            borderRadius: 10,
                            padding: 12,
                            background: "white",
                        }}
                    >
                        <div style={{ display: "flex", justifyContent: "space-between", gap: 10 }}>
                            <div>
                                <div style={{ fontWeight: 700 }}>{r.title}</div>
                                <div style={{ opacity: 0.75, fontSize: 12 }}>{r.slug}</div>
                            </div>

                            {/* Solo tiene sentido en modo foco */}
                            {routes.focusBbox && (
                                <button
                                    onClick={() =>
                                        setRoutesSelected(isSelected ? null : { slug: r.slug, polyline: null })
                                    }
                                    style={{
                                        padding: "8px 10px",
                                        border: "1px solid #ddd",
                                        borderRadius: 8,
                                        background: isSelected ? "#111" : "white",
                                        color: isSelected ? "white" : "#111",
                                        cursor: "pointer",
                                        height: 36,
                                        alignSelf: "start",
                                        display: "flex",
                                        alignItems: "center",
                                        gap: 5
                                    }}
                                    title={isSelected ? "Quitar trazado" : "Ver trazado"}
                                >
                                    <span>{isSelected ? "👁️‍🗨️" : "👁️"}</span>
                                    <span style={{ fontSize: 12 }}>{isSelected ? "Quitar" : "Ver"}</span>
                                </button>
                            )}
                        </div>

                        <div style={{ marginTop: 10, display: "flex", gap: 12, opacity: 0.85, fontSize: 13 }}>
                            <div>Dist: {(r.distanceM / 1000).toFixed(1)} km</div>
                            <div>+{r.elevationGainM} m</div>
                            <div>-{r.elevationLossM} m</div>
                        </div>
                    </div>
                );
            })}
        </div>
    );
}
