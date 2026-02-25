import { useState } from "react";
import { useDebounce } from "../../../shared/hooks/useDebounce";
import { Loader } from "../../../shared/ui/Loader";
import { ErrorState } from "../../../shared/ui/ErrorState";
import { useShopStore } from "../../../store/shopStore";
import { useRoutesFiltersQuery } from "../../../features/routes/queries/useRoutesFiltersQuery";
import { useRoutesListQuery } from "../../../features/routes/queries/useRoutesListQuery";
import { RoutesList } from "../../../features/routes/ui/RoutesList";

/* ─── Difficulty ─────────────────────────────────────────────────────────── */
const DIFFICULTY_OPTIONS = [
    { value: "EASY", label: "Fácil", idle: "bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800", active: "bg-emerald-500 text-white border-emerald-500 shadow-md shadow-emerald-200 dark:shadow-emerald-900/50" },
    { value: "MODERATE", label: "Moderada", idle: "bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800", active: "bg-amber-500 text-white border-amber-500 shadow-md shadow-amber-200 dark:shadow-amber-900/50" },
    { value: "HARD", label: "Difícil", idle: "bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-800", active: "bg-orange-500 text-white border-orange-500 shadow-md shadow-orange-200 dark:shadow-orange-900/50" },
    { value: "EXPERT", label: "Experto", idle: "bg-red-50 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800", active: "bg-red-500 text-white border-red-500 shadow-md shadow-red-200 dark:shadow-red-900/50" },
] as const;

/* ─── Route Type ─────────────────────────────────────────────────────────── */
const ROUTE_TYPE_OPTIONS = [
    { value: "CIRCULAR", label: "Circular", icon: "loop", idle: "bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800", active: "bg-blue-500 text-white border-blue-500 shadow-md shadow-blue-200 dark:shadow-blue-900/50" },
    { value: "LINEAR", label: "Lineal", icon: "arrow_forward", idle: "bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800", active: "bg-amber-500 text-white border-amber-500 shadow-md shadow-amber-200 dark:shadow-amber-900/50" },
    { value: "ROUND_TRIP", label: "Ida y vuelta", icon: "sync_alt", idle: "bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-900/30 dark:text-teal-400 dark:border-teal-800", active: "bg-teal-500 text-white border-teal-500 shadow-md shadow-teal-200 dark:shadow-teal-900/50" },
] as const;

/* ─── Shared styles ──────────────────────────────────────────────────────── */
const INPUT_CLS = "w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs py-2 px-2.5 focus:outline-none focus:ring-2 focus:ring-primary/30 text-slate-900 dark:text-white placeholder:text-slate-400";
const SECTION_LABEL = "block text-[9px] font-extrabold text-slate-400 uppercase tracking-[0.12em] mb-2";

/* ─── Component ──────────────────────────────────────────────────────────── */
export function RoutesSidebarPanel() {
    const bbox = useShopStore((s) => s.bbox);
    const routes = useShopStore((s) => s.routes);
    const setRoutes = useShopStore((s) => s.setRoutes);

    const [showFilters, setShowFilters] = useState(false);

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
        difficulty: routes.difficulty,
        routeType: routes.routeType,
        durationMin: routes.durationMin,
        durationMax: routes.durationMax,
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
        difficulty: routes.difficulty,
        routeType: routes.routeType,
        durationMin: routes.durationMin,
        durationMax: routes.durationMax,
        sort: routes.sort,
        order: routes.order,
        page: routes.page,
        limit: 20,
    });

    const hasGeo = !!bbox || !!routes.focusBbox;

    // Badge en el botón de filtros avanzados (excluye q y sportCode que son siempre visibles)
    const advancedFiltersCount = [
        routes.difficulty,
        routes.routeType,
        routes.distanceMin,
        routes.distanceMax,
        routes.gainMin,
        routes.gainMax,
        routes.durationMin,
        routes.durationMax,
    ].filter((v) => v !== null && v !== undefined).length;

    // Total para el botón de reset (todos los filtros activos)
    const activeFiltersCount = advancedFiltersCount + [routes.sportCode, routes.q]
        .filter((v) => v !== null && v !== undefined && v !== "").length;

    const handleReset = () =>
        setRoutes({
            q: "",
            sportCode: null,
            difficulty: null,
            routeType: null,
            distanceMin: null,
            distanceMax: null,
            gainMin: null,
            gainMax: null,
            durationMin: null,
            durationMax: null,
            sort: "recent",
            order: "desc",
            page: 1,
        });

    return (
        <div className="flex flex-col h-full bg-white dark:bg-background-dark overflow-hidden font-marketplace text-slate-900 dark:text-white transition-colors duration-300">

            {/* ── STICKY HEADER ─────────────────────────────────────────── */}
            <div className="sticky top-0 z-20 bg-white/95 dark:bg-background-dark/95 backdrop-blur border-b border-slate-100 dark:border-slate-800">
                <div className="p-3 space-y-2.5">

                    {/* Row 1: Search + Filters toggle + Reset */}
                    <div className="flex items-center gap-2">
                        <div className="relative flex-1 flex items-center bg-slate-50 dark:bg-slate-800 rounded-xl px-3 py-2 border border-slate-200 dark:border-slate-700 focus-within:border-primary/60 focus-within:ring-2 focus-within:ring-primary/10 transition-all">
                            <span className="material-symbols-outlined text-slate-400 !text-[17px] flex-shrink-0">search</span>
                            <input
                                className="bg-transparent border-none focus:ring-0 text-sm w-full placeholder:text-slate-400 pl-2 font-medium text-slate-900 dark:text-white"
                                placeholder="Buscar rutas, cimas..."
                                type="text"
                                value={routes.q}
                                onChange={(e) => setRoutes({ q: e.target.value, page: 1 })}
                            />
                            {routes.q && (
                                <button onClick={() => setRoutes({ q: "", page: 1 })} className="text-slate-400 hover:text-slate-600 transition-colors">
                                    <span className="material-symbols-outlined !text-[17px]">close</span>
                                </button>
                            )}
                        </div>

                        {/* Filters toggle */}
                        <button
                            onClick={() => setShowFilters((v) => !v)}
                            title="Filtros avanzados"
                            className={`relative flex-shrink-0 flex items-center justify-center w-9 h-9 rounded-xl border transition-all ${showFilters || advancedFiltersCount > 0
                                ? "bg-primary text-white border-primary shadow-sm shadow-primary/30"
                                : "bg-white dark:bg-slate-900 text-slate-500 border-slate-200 dark:border-slate-700 hover:border-primary/50"
                                }`}
                        >
                            <span className="material-symbols-outlined !text-[18px]">tune</span>
                            {advancedFiltersCount > 0 && (
                                <span className="absolute -top-1.5 -right-1.5 min-w-[16px] h-4 bg-red-500 text-white text-[9px] font-extrabold rounded-full flex items-center justify-center px-0.5 leading-none">
                                    {advancedFiltersCount}
                                </span>
                            )}
                        </button>

                        {/* Reset all */}
                        {activeFiltersCount > 0 && (
                            <button
                                onClick={handleReset}
                                title="Limpiar todos los filtros"
                                className="flex-shrink-0 flex items-center justify-center w-9 h-9 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-500 border border-red-200 dark:border-red-800 hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors"
                            >
                                <span className="material-symbols-outlined !text-[18px]">filter_alt_off</span>
                            </button>
                        )}
                    </div>

                    {/* Row 2: Sort tabs */}
                    <div className="flex items-center gap-0.5 bg-slate-100 dark:bg-slate-800 rounded-lg p-0.5">
                        {(["recent", "distance", "gain"] as const).map((s) => (
                            <button
                                key={s}
                                onClick={() => setRoutes({ sort: s, page: 1 })}
                                className={`flex-1 py-1.5 rounded-md text-[9px] font-extrabold uppercase tracking-widest transition-all ${routes.sort === s
                                    ? "bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm"
                                    : "text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                                    }`}
                            >
                                {s === "recent" ? "Recientes" : s === "distance" ? "Distancia" : "Desnivel"}
                            </button>
                        ))}
                    </div>

                    {/* Row 3: Sport pills */}
                    {(filtersQuery.data?.sports ?? []).length > 0 && (
                        <div className="flex items-center gap-1.5 overflow-x-auto pb-0.5 hide-scrollbar">
                            <button
                                onClick={() => setRoutes({ sportCode: null, page: 1 })}
                                className={`flex-shrink-0 flex items-center gap-1 px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-widest border transition-all ${!routes.sportCode
                                    ? "bg-primary text-white border-primary shadow-sm shadow-primary/30"
                                    : "bg-white dark:bg-slate-900 text-slate-500 border-slate-200 dark:border-slate-700 hover:border-primary/40"
                                    }`}
                            >
                                <span className="material-symbols-outlined !text-[11px]">explore</span>
                                Todas
                            </button>
                            {filtersQuery.data?.sports.map((sport) => (
                                <button
                                    key={sport.code}
                                    onClick={() => setRoutes({ sportCode: routes.sportCode === sport.code ? null : sport.code, page: 1 })}
                                    className={`flex-shrink-0 px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-widest border transition-all ${routes.sportCode === sport.code
                                        ? "bg-slate-900 text-white dark:bg-white dark:text-slate-900 border-transparent"
                                        : "bg-white dark:bg-slate-900 text-slate-500 border-slate-200 dark:border-slate-700 hover:border-slate-400"
                                        }`}
                                >
                                    {sport.name}
                                    <span className="opacity-40"> ({sport.count})</span>
                                </button>
                            ))}
                        </div>
                    )}
                </div>

                {/* ── ADVANCED FILTERS (colapsable) ────────────────────── */}
                {showFilters && (
                    <div className="px-3 pb-3 space-y-3 border-t border-slate-100 dark:border-slate-800 pt-3 animate-in slide-in-from-top-1 fade-in duration-150">

                        {/* Difficulty */}
                        <div>
                            <p className={SECTION_LABEL}>Dificultad</p>
                            <div className="flex flex-wrap gap-1.5">
                                {DIFFICULTY_OPTIONS.map((opt) => {
                                    const isActive = routes.difficulty === opt.value;
                                    return (
                                        <button
                                            key={opt.value}
                                            onClick={() => setRoutes({ difficulty: isActive ? null : opt.value, page: 1 })}
                                            className={`px-3 py-1.5 rounded-full text-[10px] font-bold border transition-all ${isActive ? opt.active : opt.idle + " hover:opacity-75"}`}
                                        >
                                            {opt.label}
                                        </button>
                                    );
                                })}
                            </div>
                        </div>

                        {/* Route type */}
                        <div>
                            <p className={SECTION_LABEL}>Tipo de ruta</p>
                            <div className="flex flex-wrap gap-1.5">
                                {ROUTE_TYPE_OPTIONS.map((opt) => {
                                    const isActive = routes.routeType === opt.value;
                                    return (
                                        <button
                                            key={opt.value}
                                            onClick={() => setRoutes({ routeType: isActive ? null : opt.value, page: 1 })}
                                            className={`flex items-center gap-1 px-3 py-1.5 rounded-full text-[10px] font-bold border transition-all ${isActive ? opt.active : opt.idle + " hover:opacity-75"}`}
                                        >
                                            <span className="material-symbols-outlined !text-[12px]">{opt.icon}</span>
                                            {opt.label}
                                        </button>
                                    );
                                })}
                            </div>
                        </div>

                        {/* Duration */}
                        <div>
                            <p className={SECTION_LABEL}>Duración (horas)</p>
                            <div className="flex items-center gap-2">
                                <input type="number" min={0} placeholder="Mín" className={INPUT_CLS}
                                    value={routes.durationMin !== null ? Math.round(routes.durationMin / 3600) : ""}
                                    onChange={(e) => setRoutes({ durationMin: e.target.value ? Number(e.target.value) * 3600 : null, page: 1 })}
                                />
                                <span className="text-slate-300 dark:text-slate-600 text-xs font-bold flex-shrink-0">—</span>
                                <input type="number" min={0} placeholder="Máx" className={INPUT_CLS}
                                    value={routes.durationMax !== null ? Math.round(routes.durationMax / 3600) : ""}
                                    onChange={(e) => setRoutes({ durationMax: e.target.value ? Number(e.target.value) * 3600 : null, page: 1 })}
                                />
                            </div>
                        </div>

                        {/* Distance + Gain */}
                        <div className="grid grid-cols-2 gap-2">
                            <div>
                                <p className={SECTION_LABEL}>Distancia (km)</p>
                                <div className="flex items-center gap-1">
                                    <input type="number" min={0} placeholder="Mín" className={INPUT_CLS}
                                        value={routes.distanceMin !== null ? Math.round(routes.distanceMin / 1000) : ""}
                                        onChange={(e) => setRoutes({ distanceMin: e.target.value ? Number(e.target.value) * 1000 : null, page: 1 })}
                                    />
                                    <span className="text-slate-300 dark:text-slate-600 text-[10px] font-bold flex-shrink-0">—</span>
                                    <input type="number" min={0} placeholder="Máx" className={INPUT_CLS}
                                        value={routes.distanceMax !== null ? Math.round(routes.distanceMax / 1000) : ""}
                                        onChange={(e) => setRoutes({ distanceMax: e.target.value ? Number(e.target.value) * 1000 : null, page: 1 })}
                                    />
                                </div>
                            </div>
                            <div>
                                <p className={SECTION_LABEL}>Desnivel (m)</p>
                                <div className="flex items-center gap-1">
                                    <input type="number" min={0} placeholder="Mín" className={INPUT_CLS}
                                        value={routes.gainMin ?? ""}
                                        onChange={(e) => setRoutes({ gainMin: e.target.value ? Number(e.target.value) : null, page: 1 })}
                                    />
                                    <span className="text-slate-300 dark:text-slate-600 text-[10px] font-bold flex-shrink-0">—</span>
                                    <input type="number" min={0} placeholder="Máx" className={INPUT_CLS}
                                        value={routes.gainMax ?? ""}
                                        onChange={(e) => setRoutes({ gainMax: e.target.value ? Number(e.target.value) : null, page: 1 })}
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                )}
            </div>

            {/* ── SCROLLABLE RESULTS ──────────────────────────────────── */}
            <div className="flex-1 overflow-y-auto p-3 md:p-4 hide-scrollbar">
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
                        <div className="flex justify-between items-center mb-3">
                            <h3 className="text-sm font-bold flex items-center gap-1.5 text-slate-900 dark:text-white leading-tight">
                                <span className="material-symbols-outlined text-primary !text-[16px]">local_fire_department</span>
                                {routes.q ? `"${routes.q}"` : "Rutas destacadas"}
                            </h3>
                            <span className="text-[9px] text-slate-400 font-extrabold uppercase tracking-widest whitespace-nowrap">
                                {listQuery.data?.total ?? 0} encontradas
                            </span>
                        </div>
                        <RoutesList items={listQuery.data?.items ?? []} />
                    </div>
                )}
            </div>
        </div>
    );
}