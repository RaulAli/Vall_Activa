import type { OfferListItem } from "../domain/types";
import { OfferCard } from "./OfferCard";

export function OffersList({ items }: { items: OfferListItem[] }) {
    if (items.length === 0) {
        return (
            <div className="py-12 text-center text-slate-400 italic text-sm">
                No hay ofertas disponibles en esta zona.
            </div>
        );
    }

    return (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 pb-8">
            {items.map((it) => (
                <OfferCard key={it.id} item={it} />
            ))}
        </div>
    );
}
