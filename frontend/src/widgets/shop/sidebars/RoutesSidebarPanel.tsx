import { useState } from "react";
import { useDebounce } from "../../../shared/hooks/useDebounce";
import { Loader } from "../../../shared/ui/Loader";
import { ErrorState } from "../../../shared/ui/ErrorState";
import { useShopStore } from "../../../store/shopStore";

import { useRoutesFiltersQuery } from "../../../features/routes/queries/useRoutesFiltersQuery";
import { useRoutesListQuery } from "../../../features/routes/queries/useRoutesListQuery";
import { RoutesList } from "../../../features/routes/ui/RoutesList";

export function RoutesSidebarPanel() {
    const bbox = useShopStore((s) => s.bbox);
    const routes = useShopStore((s) => s.routes);
    const setRoutes = useShopStore((s) => s.setRoutes);
    const [showAdvanced, setShowAdvanced] = useState(false);

    const qDebounced = useDebounce(routes.q, 250);

    const filtersQuery = useRoutesFiltersQuery({
        bbox,
        focusBbox: routes.focusBbox,
        q: qDebounced,
        sportCode: routes.sportCode,
        distanceMin: routes.distanceMin,
        distanceMax: routes.distanceMax,
        gainMin: routes.gainMin,
        gainMax: routes.gainMax,
    });

    const listQuery = useRoutesListQuery({
        bbox,
        focusBbox: routes.focusBbox,
        q: qDebounced,
        sportCode: routes.sportCode,
        distanceMin: routes.distanceMin,
        distanceMax: routes.distanceMax,
        gainMin: routes.gainMin,
        gainMax: routes.gainMax,
        sort: routes.sort,
        order: routes.order,
        page: routes.page,
        limit: 20,
    });

    const hasGeo = !!bbox || !!routes.focusBbox;

    return (
        <div className="flex flex-col h-full bg-white dark:bg-background-dark overflow-hidden font-marketplace text-slate-900 dark:text-white transition-colors duration-300">
            {/* Toolbar: Search + Advanced Filters */}
            <div className="sticky top-0 z-20 bg-white/95 dark:bg-background-dark/95 backdrop-blur shadow-sm border-b border-slate-100 dark:border-slate-800">
                <div className="p-4 space-y-3">
                    {/* Search Field */}
                    <div className="flex items-center gap-3">
                        <div className="relative flex-1 flex items-center bg-slate-50 dark:bg-slate-800 rounded-xl px-4 py-2 border border-slate-200 dark:border-slate-700 focus-within:border-primary/50 transition-all shadow-sm">
                            <span className="material-symbols-outlined text-slate-400 !text-lg">search</span>
                            <input
                                className="bg-transparent border-none focus:ring-0 text-sm w-full placeholder:text-slate-400 pl-3 font-medium text-slate-900 dark:text-white"
                                placeholder="Buscar rutas, cimas..."
                                type="text"
                                value={routes.q}
                                onChange={(e) => setRoutes({ q: e.target.value })}
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
                                <label className="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Distancia Max (Km)</label>
                                <input
                                    type="number"
                                    placeholder="Libre"
                                    className="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-xs py-2 px-3 focus:ring-1 focus:ring-primary/50 text-slate-900 dark:text-white"
                                    value={routes.distanceMax ? Math.round(routes.distanceMax / 1000) : ""}
                                    onChange={(e) => setRoutes({ distanceMax: e.target.value ? Number(e.target.value) * 1000 : null })}
                                />
                            </div>
                            <div>
                                <label className="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Desnivel Max (m)</label>
                                <input
                                    type="number"
                                    placeholder="Libre"
                                    className="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-xs py-2 px-3 focus:ring-1 focus:ring-primary/50 text-slate-900 dark:text-white"
                                    value={routes.gainMax || ""}
                                    onChange={(e) => setRoutes({ gainMax: e.target.value ? Number(e.target.value) : null })}
                                />
                            </div>
                        </div>
                    )}

                    {/* Quick Filters Pill Bar */}
                    <div className="flex items-center gap-2 overflow-x-auto pb-1 hide-scrollbar font-display">
                        <button className="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-primary text-white text-[10px] font-bold uppercase tracking-widest transition-all border border-primary shadow-lg shadow-primary/20">
                            <span className="material-symbols-outlined !text-[14px]">explore</span>
                            Todas
                        </button>

                        <div className="h-4 w-px bg-slate-200 dark:bg-slate-800 mx-1 flex-shrink-0" />

                        {filtersQuery.data?.sports.map((sport) => (
                            <button
                                key={sport.code}
                                onClick={() => setRoutes({ sportCode: routes.sportCode === sport.code ? null : sport.code })}
                                className={`flex items-center gap-1.5 px-4 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-widest transition-all border ${routes.sportCode === sport.code
                                    ? "bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900"
                                    : "bg-white dark:bg-slate-900 text-slate-500 border-slate-200 dark:border-slate-700"
                                    }`}
                            >
                                {sport.name}
                                <span className="opacity-40 text-[9px] font-medium">({sport.count})</span>
                            </button>
                        ))}
                    </div>
                </div>
            </div>

            {/* Scrollable Content */}
            <div className="flex-1 overflow-y-auto p-4 md:p-6 space-y-8 hide-scrollbar">
                {!hasGeo ? (
                    <div className="flex flex-col items-center justify-center h-full text-center opacity-50 py-20">
                        <span className="material-symbols-outlined !text-4xl mb-2 text-primary">map_location_dot</span>
                        <p className="text-sm font-medium text-slate-500">Explora el mapa para encontrar rutas</p>
                    </div>
                ) : listQuery.isLoading ? (
                    <div className="flex justify-center py-20"><Loader /></div>
                ) : listQuery.error ? (
                    <ErrorState message="Error al cargar las rutas" />
                ) : (
                    <div className="pb-10">
                        <div className="flex justify-between items-end mb-6">
                            <h3 className="text-xl font-bold flex items-center gap-2 text-slate-900 dark:text-white leading-tight">
                                <span className="material-symbols-outlined text-primary">local_fire_department</span>
                                {routes.q ? `Resultados para "${routes.q}"` : "Rutas destacadas"}
                            </h3>
                            <span className="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest mb-1 whitespace-nowrap">
                                {listQuery.data?.total || 0} encontradas
                            </span>
                        </div>
                        <RoutesList items={listQuery.data?.items || []} />
                    </div>
                )}
            </div>
        </div>
    );
}
