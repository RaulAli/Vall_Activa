import { useState } from "react";
import { useDebounce } from "../../../shared/hooks/useDebounce";
import { Loader } from "../../../shared/ui/Loader";
import { ErrorState } from "../../../shared/ui/ErrorState";
import { useShopStore } from "../../../store/shopStore";

import { useOffersFiltersQuery } from "../../../features/offers/queries/useOffersFiltersQuery";
import { useOffersListQuery } from "../../../features/offers/queries/useOffersListQuery";
import { OffersList } from "../../../features/offers/ui/OffersList";

export function OffersSidebarPanel() {
    const bbox = useShopStore((s) => s.bbox);
    const offers = useShopStore((s) => s.offers);
    const setOffers = useShopStore((s) => s.setOffers);
    const [showAdvanced, setShowAdvanced] = useState(false);

    const qDebounced = useDebounce(offers.q, 250);

    const filtersQuery = useOffersFiltersQuery({
        bbox,
        q: qDebounced,
        discountType: offers.discountType,
        inStock: offers.inStock,
    });

    const listQuery = useOffersListQuery({
        bbox,
        q: qDebounced,
        discountType: offers.discountType,
        inStock: offers.inStock,
        priceMin: offers.priceMin,
        priceMax: offers.priceMax,
        pointsMin: offers.pointsMin,
        pointsMax: offers.pointsMax,
        sort: offers.sort,
        order: offers.order,
        page: offers.page,
        limit: 20,
    });

    const hasGeo = !!bbox;

    return (
        <div className="flex flex-col h-full bg-white dark:bg-background-dark overflow-hidden transition-colors duration-300">
            {/* Toolbar: Search + Filters */}
            <div className="sticky top-0 z-20 bg-white/95 dark:bg-background-dark/95 backdrop-blur shadow-sm border-b border-slate-100 dark:border-slate-800">
                <div className="p-4 space-y-3">
                    {/* Search Field */}
                    <div className="flex items-center gap-3">
                        <div className="relative flex-1 flex items-center bg-slate-50 dark:bg-slate-800 rounded-xl px-4 py-2 border border-slate-200 dark:border-slate-700 focus-within:border-primary/50 transition-all shadow-sm">
                            <span className="material-symbols-outlined text-slate-400 !text-lg">search</span>
                            <input
                                className="bg-transparent border-none focus:ring-0 text-sm w-full placeholder:text-slate-400 pl-3 font-medium text-slate-900 dark:text-white"
                                placeholder="Buscar ofertas..."
                                type="text"
                                value={offers.q}
                                onChange={(e) => setOffers({ q: e.target.value })}
                            />
                        </div>
                        <button
                            onClick={() => setShowAdvanced(!showAdvanced)}
                            className={`p-2 rounded-xl border transition-all ${showAdvanced ? "bg-primary text-white border-primary" : "bg-white dark:bg-slate-900 text-slate-600 border-slate-200 dark:border-slate-700 hover:border-primary/50"}`}
                        >
                            <span className="material-symbols-outlined !text-xl">tune</span>
                        </button>
                    </div>

                    {/* Advanced Filters Section */}
                    {showAdvanced && (
                        <div className="grid grid-cols-2 gap-4 pt-2 border-t border-slate-100 dark:border-slate-800 animate-in fade-in slide-in-from-top-2 duration-200">
                            <div>
                                <label className="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Precio Max (€)</label>
                                <input
                                    type="number"
                                    placeholder="Libre"
                                    className="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-xs py-2 px-3 focus:ring-1 focus:ring-primary/50"
                                    value={offers.priceMax || ""}
                                    onChange={(e) => setOffers({ priceMax: e.target.value ? Number(e.target.value) : null })}
                                />
                            </div>
                            <div>
                                <label className="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Puntos VAC Max</label>
                                <input
                                    type="number"
                                    placeholder="Libre"
                                    className="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-xs py-2 px-3 focus:ring-1 focus:ring-primary/50"
                                    value={offers.pointsMax || ""}
                                    onChange={(e) => setOffers({ pointsMax: e.target.value ? Number(e.target.value) : null })}
                                />
                            </div>
                        </div>
                    )}

                    {/* Quick Filters Pill Bar */}
                    <div className="flex items-center gap-2 overflow-x-auto pb-1 hide-scrollbar">
                        <button
                            onClick={() => setOffers({ inStock: !offers.inStock })}
                            className={`flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-all border ${offers.inStock
                                ? "bg-primary text-white border-primary"
                                : "bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700"
                                }`}
                        >
                            <span className="material-symbols-outlined !text-[14px]">inventory_2</span>
                            Stock
                        </button>

                        <div className="h-4 w-px bg-slate-200 dark:bg-slate-800 mx-1 flex-shrink-0" />

                        {filtersQuery.data?.discountTypes.map((dt) => (
                            <button
                                key={dt.value}
                                onClick={() => setOffers({ discountType: offers.discountType === dt.value ? null : dt.value })}
                                className={`flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-all border ${offers.discountType === dt.value
                                    ? "bg-slate-900 text-white dark:bg-white dark:text-slate-900"
                                    : "bg-white dark:bg-slate-900 text-slate-500 border-slate-200 dark:border-slate-700"
                                    }`}
                            >
                                {dt.value}
                                <span className="opacity-40 text-[9px]">({dt.count})</span>
                            </button>
                        ))}
                    </div>
                </div>
            </div>

            {/* Scrollable Content */}
            <div className="flex-1 overflow-y-auto p-4 md:p-6 space-y-8 hide-scrollbar">
                {!hasGeo ? (
                    <div className="flex flex-col items-center justify-center h-full text-center opacity-50 py-20">
                        <span className="material-symbols-outlined !text-4xl mb-2 text-primary">map</span>
                        <p className="text-sm font-medium">Mueve el mapa para explorar ofertas</p>
                    </div>
                ) : listQuery.isLoading ? (
                    <div className="flex justify-center py-20"><Loader /></div>
                ) : listQuery.error ? (
                    <ErrorState message="Error al cargar las ofertas" />
                ) : (
                    <div className="pb-10">
                        <div className="flex justify-between items-end mb-6">
                            <h3 className="text-xl font-bold flex items-center gap-2 text-slate-900 dark:text-white">
                                <span className="material-symbols-outlined text-primary">auto_awesome</span>
                                {offers.q ? `Resultados para "${offers.q}"` : "Ofertas premium"}
                            </h3>
                            <span className="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest mb-1">
                                {listQuery.data?.total || 0} encontradas
                            </span>
                        </div>
                        <OffersList items={listQuery.data?.items || []} />
                    </div>
                )}
            </div>
        </div>
    );
}
