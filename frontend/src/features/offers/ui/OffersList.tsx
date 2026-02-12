import type { OfferListItem } from "../domain/types";
import { OfferCard } from "./OfferCard";

export function OffersList({ items }: { items: OfferListItem[] }) {
    return (
        <div>
            {items.map((it) => (
                <OfferCard key={it.id} item={it} />
            ))}
        </div>
    );
}
