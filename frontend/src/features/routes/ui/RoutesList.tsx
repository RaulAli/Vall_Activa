import type { RouteListItem } from "../domain/types";
import { RouteCard } from "./RouteCard";

export function RoutesList({ items }: { items: RouteListItem[] }) {
    return (
        <div>
            {items.map((it) => (
                <RouteCard key={it.id} item={it} />
            ))}
        </div>
    );
}
