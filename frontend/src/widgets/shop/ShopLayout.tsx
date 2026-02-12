import { ReactNode } from "react";

export function ShopLayout({ sidebar, map }: { sidebar: ReactNode; map: ReactNode }) {
    return (
        <div style={{ display: "grid", gridTemplateColumns: "420px 1fr", height: "100vh" }}>
            <div style={{ borderRight: "1px solid #eee", overflow: "auto", padding: 14 }}>
                {sidebar}
            </div>
            <div style={{ height: "100vh" }}>{map}</div>
        </div>
    );
}
