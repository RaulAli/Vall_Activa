import type { RouteListItem } from "../domain/types";

export function RouteCard({ item }: { item: RouteListItem }) {
  const km = Math.round(item.distanceM / 1000);

  return (
    <div style={{ border: "1px solid #ddd", borderRadius: 10, padding: 12, marginBottom: 10 }}>
      <div style={{ fontWeight: 700 }}>{item.title}</div>
      <div style={{ marginTop: 6, opacity: 0.8 }}>
        {km} km · +{item.elevationGainM} m · -{item.elevationLossM} m
      </div>
      <div style={{ marginTop: 6, fontSize: 12, opacity: 0.7 }}>{item.slug}</div>
    </div>
  );
}
