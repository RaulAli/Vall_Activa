import type { RouteFiltersMeta } from "../domain/types";

export function RoutesFiltersPanel({
    meta,
    q,
    sportCode,
    onChangeQ,
    onChangeSportCode,
}: {
    meta: RouteFiltersMeta;
    q: string;
    sportCode: string | null;
    onChangeQ: (v: string) => void;
    onChangeSportCode: (v: string | null) => void;
}) {
    return (
        <div style={{ borderBottom: "1px solid #eee", paddingBottom: 12, marginBottom: 12 }}>
            <input
                value={q}
                onChange={(e) => onChangeQ(e.target.value)}
                placeholder="Buscar rutas..."
                style={{ width: "100%", padding: 8, borderRadius: 8, border: "1px solid #ddd" }}
            />

            <div style={{ marginTop: 10, fontSize: 12, opacity: 0.8 }}>
                Distance: {meta.distance.min ?? "—"} - {meta.distance.max ?? "—"} · Gain: {meta.gain.min ?? "—"} -{" "}
                {meta.gain.max ?? "—"} · Routes: {meta.counts.routes}
            </div>

            <div style={{ marginTop: 10 }}>
                <div style={{ fontWeight: 600, marginBottom: 6 }}>Sport</div>
                <div style={{ display: "flex", gap: 8, flexWrap: "wrap" }}>
                    <button onClick={() => onChangeSportCode(null)} style={{ padding: "6px 10px" }}>
                        All
                    </button>

                    {meta.sports.map((s) => (
                        <button
                            key={s.code}
                            onClick={() => onChangeSportCode(s.code)}
                            style={{
                                padding: "6px 10px",
                                border: sportCode === s.code ? "2px solid #333" : "1px solid #ddd",
                                borderRadius: 8,
                            }}
                        >
                            {s.name} ({s.count})
                        </button>
                    ))}
                </div>
            </div>
        </div>
    );
}
