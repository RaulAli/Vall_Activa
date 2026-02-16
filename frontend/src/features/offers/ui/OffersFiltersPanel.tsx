import type { OfferFiltersMeta } from "../domain/types";

type Props = {
    meta: OfferFiltersMeta;
    q: string;
    discountType: string | null;
    inStock: boolean;
    onChangeQ: (v: string) => void;
    onToggleInStock: () => void;
    onChangeDiscountType: (v: string | null) => void;
};

export function OffersFiltersPanel({
    q,
    onChangeQ,
}: Props) {
    return (
        <div className="mb-6 space-y-4">
            <div className="relative flex items-center bg-slate-100 dark:bg-slate-800 rounded-xl px-4 py-2 border border-transparent focus-within:border-primary/50 transition-all shadow-sm">
                <span className="material-symbols-outlined text-slate-400 !text-xl">search</span>
                <input
                    className="bg-transparent border-none focus:ring-0 text-sm w-full placeholder:text-slate-400 pl-3 font-medium"
                    placeholder="Buscar ofertas, productos..."
                    type="text"
                    value={q}
                    onChange={(e) => onChangeQ(e.target.value)}
                />
            </div>
        </div>
    );
}
