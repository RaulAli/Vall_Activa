import { useState, useMemo } from "react";
import { useNavigate } from "react-router-dom";
import { Header } from "../widgets/layout/Header";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { useAuthStore } from "../store/authStore";
import { getMyRoutes, updateRoute } from "../features/routes/api/routeApi";
import { getFallbackImage } from "../shared/utils/images";
import { http } from "../shared/api/http";
import { endpoints } from "../shared/api/endpoints";
import type { MyRouteItem } from "../features/routes/domain/types";

function formatDuration(seconds: number): string {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    if (h === 0) return `${m} min`;
    return m > 0 ? `${h}h ${m}min` : `${h}h`;
}

type SportOption = { id: string; code: string; name: string };

const SPORT_ICONS: Record<string, string> = {
    HIKE: "hiking",
    BIKE: "directions_bike",
    RUN: "directions_run",
    SKI: "downhill_skiing",
    CLIMB: "landscape",
    KAYAK: "kayaking",
    SURF: "surfing",
    SWIM: "pool",
};

const VISIBILITY_CONFIG = {
    PUBLIC: { label: "Pública", icon: "public", color: "text-emerald-600 dark:text-emerald-400", bg: "bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800" },
    UNLISTED: { label: "No listada", icon: "link", color: "text-amber-600 dark:text-amber-400", bg: "bg-amber-50 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800" },
    PRIVATE: { label: "Privada", icon: "lock", color: "text-slate-500 dark:text-slate-400", bg: "bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700" },
} as const;

const STATUS_CONFIG = {
    PUBLISHED: { label: "Publicada", color: "text-blue-600 dark:text-blue-400", dot: "bg-blue-500" },
    DRAFT: { label: "Borrador", color: "text-slate-400", dot: "bg-slate-400" },
    ARCHIVED: { label: "Archivada", color: "text-slate-400", dot: "bg-slate-300" },
} as const;

const DIFFICULTY_CONFIG = {
    EASY: { label: "Fácil", icon: "signal_cellular_1_bar", color: "text-emerald-600 dark:text-emerald-400", bg: "bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800" },
    MODERATE: { label: "Moderada", icon: "signal_cellular_2_bar", color: "text-amber-600 dark:text-amber-400", bg: "bg-amber-50 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800" },
    HARD: { label: "Difícil", icon: "signal_cellular_3_bar", color: "text-orange-600 dark:text-orange-400", bg: "bg-orange-50 dark:bg-orange-950/30 border-orange-200 dark:border-orange-800" },
    EXPERT: { label: "Experto", icon: "signal_cellular_4_bar", color: "text-red-600 dark:text-red-400", bg: "bg-red-50 dark:bg-red-950/30 border-red-200 dark:border-red-800" },
} as const;

type DifficultyKey = keyof typeof DIFFICULTY_CONFIG;

const ROUTE_TYPE_CONFIG = {
    CIRCULAR: { label: "Circular", icon: "loop", color: "text-blue-600 dark:text-blue-400", bg: "bg-blue-50 dark:bg-blue-950/30 border-blue-200 dark:border-blue-800" },
    LINEAR: { label: "Lineal", icon: "trending_flat", color: "text-amber-600 dark:text-amber-400", bg: "bg-amber-50 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800" },
    ROUND_TRIP: { label: "Ida y vuelta", icon: "sync_alt", color: "text-teal-600 dark:text-teal-400", bg: "bg-teal-50 dark:bg-teal-950/30 border-teal-200 dark:border-teal-800" },
} as const;
type RouteTypeKey = keyof typeof ROUTE_TYPE_CONFIG;

type VisibilityFilter = "ALL" | "PUBLIC" | "UNLISTED" | "PRIVATE";

export function MyRoutesPage() {
    const navigate = useNavigate();
    const { token } = useAuthStore();
    const queryClient = useQueryClient();

    const PAGE_SIZE = 10;

    const [visFilter, setVisFilter] = useState<VisibilityFilter>("ALL");
    const [editingId, setEditingId] = useState<string | null>(null);
    const [page, setPage] = useState(1);

    // ── Filters ──
    const [q, setQ] = useState("");
    const [sportFilter, setSportFilter] = useState<string | null>(null);
    const [difficultyFilter, setDifficultyFilter] = useState<string | null>(null);
    const [routeTypeFilter, setRouteTypeFilter] = useState<string | null>(null);
    const [distanceMin, setDistanceMin] = useState("");
    const [distanceMax, setDistanceMax] = useState("");
    const [gainMin, setGainMin] = useState("");
    const [gainMax, setGainMax] = useState("");
    const [durationMin, setDurationMin] = useState("");
    const [durationMax, setDurationMax] = useState("");
    const [sort, setSort] = useState<"recent" | "distance" | "gain" | "duration">("recent");
    const [showFilters, setShowFilters] = useState(false);

    function resetFilters() {
        setQ(""); setSportFilter(null); setDifficultyFilter(null); setRouteTypeFilter(null);
        setDistanceMin(""); setDistanceMax(""); setGainMin(""); setGainMax("");
        setDurationMin(""); setDurationMax(""); setSort("recent"); setVisFilter("ALL"); setPage(1);
    }

    const { data: routes = [], isLoading, isError } = useQuery({
        queryKey: ["me", "routes"],
        queryFn: () => getMyRoutes(token!),
        enabled: !!token,
    });

    // All sports from API — used in RouteRow edit form
    const { data: sports = [] } = useQuery<SportOption[]>({
        queryKey: ["sports"],
        queryFn: () => http<SportOption[]>("GET", endpoints.sports.list),
        staleTime: Infinity,
    });

    // ── Dynamic filter options derived from user's own routes ──
    const availableSports = useMemo(() => {
        const seen = new Map<string, string>();
        routes.forEach(r => {
            if (r.sportCode && r.sportName && !seen.has(r.sportCode))
                seen.set(r.sportCode, r.sportName);
        });
        return Array.from(seen.entries()).map(([code, name]) => ({ code, name }));
    }, [routes]);

    const availableDifficulties = useMemo(() =>
        (Object.keys(DIFFICULTY_CONFIG) as DifficultyKey[]).filter(k =>
            routes.some(r => r.difficulty === k)
        ), [routes]);

    const availableRouteTypes = useMemo(() =>
        (Object.keys(ROUTE_TYPE_CONFIG) as RouteTypeKey[]).filter(k =>
            routes.some(r => r.routeType === k)
        ), [routes]);

    type RoutePatch = {
        title?: string;
        description?: string | null;
        sportCode?: string;
        visibility?: "PUBLIC" | "UNLISTED" | "PRIVATE";
        status?: "DRAFT" | "PUBLISHED" | "ARCHIVED";
        difficulty?: "EASY" | "MODERATE" | "HARD" | "EXPERT" | null;
        routeType?: "CIRCULAR" | "LINEAR" | "ROUND_TRIP" | null;
    };

    const updateMut = useMutation({
        mutationFn: ({ id, patch }: { id: string; patch: RoutePatch }) =>
            updateRoute(token!, id, patch),
        onMutate: async ({ id, patch }) => {
            await queryClient.cancelQueries({ queryKey: ["me", "routes"] });
            const prev = queryClient.getQueryData<MyRouteItem[]>(["me", "routes"]);
            queryClient.setQueryData<MyRouteItem[]>(["me", "routes"], (old = []) =>
                old.map(r => r.id === id ? { ...r, ...patch } : r)
            );
            return { prev };
        },
        onError: (_err, _vars, ctx) => {
            if (ctx?.prev) queryClient.setQueryData(["me", "routes"], ctx.prev);
        },
        onSettled: () => queryClient.invalidateQueries({ queryKey: ["me", "routes"] }),
    });

    const filtered = routes.filter(r => {
        if (visFilter !== "ALL" && r.visibility !== visFilter) return false;
        if (q.trim() && !r.title.toLowerCase().includes(q.trim().toLowerCase())) return false;
        if (sportFilter && r.sportCode !== sportFilter) return false;
        if (difficultyFilter && r.difficulty !== difficultyFilter) return false;
        if (routeTypeFilter && r.routeType !== routeTypeFilter) return false;
        if (distanceMin && r.distanceM < Number(distanceMin) * 1000) return false;
        if (distanceMax && r.distanceM > Number(distanceMax) * 1000) return false;
        if (gainMin && r.elevationGainM < Number(gainMin)) return false;
        if (gainMax && r.elevationGainM > Number(gainMax)) return false;
        if (durationMin && (r.durationSeconds ?? 0) < Number(durationMin) * 60) return false;
        if (durationMax && (r.durationSeconds ?? Infinity) > Number(durationMax) * 60) return false;
        return true;
    }).sort((a, b) => {
        if (sort === "distance") return b.distanceM - a.distanceM;
        if (sort === "gain") return b.elevationGainM - a.elevationGainM;
        if (sort === "duration") return (b.durationSeconds ?? 0) - (a.durationSeconds ?? 0);
        return new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime();
    });

    const advancedFiltersCount = [sportFilter, difficultyFilter, routeTypeFilter, distanceMin, distanceMax, gainMin, gainMax, durationMin, durationMax].filter(Boolean).length;
    const anyFilter = advancedFiltersCount > 0 || q.trim() || visFilter !== "ALL";
    const totalPages = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
    const safePage = Math.min(page, totalPages);
    const paginated = filtered.slice((safePage - 1) * PAGE_SIZE, safePage * PAGE_SIZE);

    const stats = {
        total: routes.length,
        public: routes.filter(r => r.visibility === "PUBLIC").length,
        unlisted: routes.filter(r => r.visibility === "UNLISTED").length,
        private: routes.filter(r => r.visibility === "PRIVATE").length,
    };

    if (!token) {
        return (
            <>
                <Header />
                <div className="min-h-screen flex items-center justify-center">
                    <p className="text-slate-500">Debes iniciar sesión.</p>
                </div>
            </>
        );
    }

    return (
        <>
            <Header />
            <div className="max-w-4xl mx-auto px-4 py-8">
                {/* Page header */}
                <div className="flex items-center gap-3 mb-6">
                    <button
                        onClick={() => navigate(-1)}
                        className="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-white transition-colors"
                        title="Volver"
                    >
                        <span className="material-symbols-outlined">arrow_back</span>
                    </button>
                    <div className="flex-1">
                        <h1 className="text-2xl font-black text-slate-900 dark:text-white">Mis rutas</h1>
                        <p className="text-sm text-slate-500 mt-0.5">{stats.total} ruta{stats.total !== 1 ? "s" : ""} en total</p>
                    </div>
                    <button
                        onClick={() => navigate("/routes/new")}
                        className="flex items-center gap-2 px-4 py-2.5 bg-primary hover:bg-blue-600 text-white text-sm font-bold rounded-xl shadow-lg shadow-primary/20 transition-all active:scale-95"
                    >
                        <span className="material-symbols-outlined !text-base">upload</span>
                        Subir nueva
                    </button>
                </div>

                {/* Stats bar */}
                <div className="grid grid-cols-3 gap-3 mb-6">
                    {([
                        { key: "PUBLIC", label: "Públicas", count: stats.public, icon: "public", color: "text-emerald-600" },
                        { key: "UNLISTED", label: "No listadas", count: stats.unlisted, icon: "link", color: "text-amber-600" },
                        { key: "PRIVATE", label: "Privadas", count: stats.private, icon: "lock", color: "text-slate-500" },
                    ] as const).map(s => (
                        <button
                            key={s.key}
                            onClick={() => setVisFilter(v => v === s.key ? "ALL" : s.key)}
                            className={`flex flex-col items-center gap-1 p-3 rounded-xl border transition-all ${visFilter === s.key
                                ? "bg-primary/5 border-primary/30 dark:bg-primary/10"
                                : "bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 hover:border-slate-300"
                                }`}
                        >
                            <span className={`material-symbols-outlined !text-lg ${s.color}`}>{s.icon}</span>
                            <span className="text-xl font-black text-slate-900 dark:text-white">{s.count}</span>
                            <span className="text-[10px] font-bold uppercase tracking-widest text-slate-400">{s.label}</span>
                        </button>
                    ))}
                </div>

                {/* Search + filter toggle */}
                <div className="flex items-center gap-2 mb-3">
                    <div className="relative flex-1">
                        <span className="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined !text-base text-slate-400">search</span>
                        <input
                            type="text"
                            value={q}
                            onChange={e => { setQ(e.target.value); setPage(1); }}
                            placeholder="Buscar mis rutas…"
                            className="w-full pl-9 pr-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </div>
                    <button
                        onClick={() => setShowFilters(v => !v)}
                        className={`relative p-2 rounded-xl border transition-all ${showFilters
                            ? "bg-primary/10 border-primary/30 text-primary"
                            : "bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-500 hover:border-slate-300"
                            }`}
                    >
                        <span className="material-symbols-outlined !text-xl">tune</span>
                        {advancedFiltersCount > 0 && (
                            <span className="absolute -top-1 -right-1 w-4 h-4 bg-primary text-white text-[9px] font-black rounded-full flex items-center justify-center">{advancedFiltersCount}</span>
                        )}
                    </button>
                    {anyFilter && (
                        <button
                            onClick={resetFilters}
                            className="p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-400 hover:text-red-500 hover:border-red-300 transition-all"
                            title="Limpiar filtros"
                        >
                            <span className="material-symbols-outlined !text-xl">filter_alt_off</span>
                        </button>
                    )}
                </div>

                {/* Visibility chips */}
                <div className="flex gap-2 mb-3 flex-wrap">
                    {(["ALL", "PUBLIC", "UNLISTED", "PRIVATE"] as VisibilityFilter[]).map(f => (
                        <button
                            key={f}
                            onClick={() => { setVisFilter(f); setPage(1); }}
                            className={`px-3 py-1.5 rounded-full text-xs font-bold border transition-all ${visFilter === f
                                ? "bg-primary text-white border-primary shadow-sm"
                                : "bg-white dark:bg-slate-900 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:border-slate-300"
                                }`}
                        >
                            {f === "ALL" ? "Todas" : VISIBILITY_CONFIG[f].label}
                        </button>
                    ))}
                </div>

                {/* Sort tabs */}
                <div className="flex gap-1 mb-3 bg-slate-100 dark:bg-slate-800 rounded-xl p-1">
                    {(["recent", "distance", "gain", "duration"] as const).map(s => (
                        <button
                            key={s}
                            onClick={() => { setSort(s); setPage(1); }}
                            className={`flex-1 py-1.5 rounded-lg text-xs font-bold transition-all ${sort === s
                                ? "bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm"
                                : "text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-white"
                                }`}
                        >
                            {{ recent: "Recientes", distance: "Distancia", gain: "Desnivel", duration: "Duración" }[s]}
                        </button>
                    ))}
                </div>

                {/* Collapsible advanced filters */}
                {showFilters && (
                    <div className="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl p-4 mb-4 space-y-4">
                        {/* Sport */}
                        {availableSports.length > 0 && (
                            <div>
                                <p className="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Deporte</p>
                                <div className="flex flex-wrap gap-1.5">
                                    {availableSports.map(s => (
                                        <button
                                            key={s.code}
                                            onClick={() => { setSportFilter(sp => sp === s.code ? null : s.code); setPage(1); }}
                                            className={`px-3 py-1 rounded-full text-xs font-bold border transition-all ${sportFilter === s.code
                                                ? "bg-primary text-white border-primary shadow-sm"
                                                : "bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-slate-300"
                                                }`}
                                        >
                                            {s.name}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* Difficulty */}
                        <div>
                            <p className="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Dificultad</p>
                            <div className="flex flex-wrap gap-1.5">
                                {availableDifficulties.map(key => {
                                    const cfg = DIFFICULTY_CONFIG[key];
                                    return (
                                        <button
                                            key={key}
                                            onClick={() => { setDifficultyFilter(d => d === key ? null : key); setPage(1); }}
                                            className={`flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold border transition-all ${difficultyFilter === key
                                                ? `${cfg.bg} ${cfg.color} shadow-sm`
                                                : "bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-slate-300"
                                                }`}
                                        >
                                            <span className={`material-symbols-outlined !text-[12px] ${difficultyFilter === key ? cfg.color : ""}`}>{cfg.icon}</span>
                                            {cfg.label}
                                        </button>
                                    );
                                })}
                            </div>
                        </div>

                        {/* Route type */}
                        <div>
                            <p className="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Tipo de ruta</p>
                            <div className="flex flex-wrap gap-1.5">
                                {availableRouteTypes.map(key => {
                                    const cfg = ROUTE_TYPE_CONFIG[key];
                                    return (
                                        <button
                                            key={key}
                                            onClick={() => { setRouteTypeFilter(t => t === key ? null : key); setPage(1); }}
                                            className={`flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold border transition-all ${routeTypeFilter === key
                                                ? `${cfg.bg} ${cfg.color} shadow-sm`
                                                : "bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-slate-300"
                                                }`}
                                        >
                                            <span className={`material-symbols-outlined !text-[12px] ${routeTypeFilter === key ? cfg.color : ""}`}>{cfg.icon}</span>
                                            {cfg.label}
                                        </button>
                                    );
                                })}
                            </div>
                        </div>

                        {/* Duration */}
                        <div>
                            <p className="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Duración (min)</p>
                            <div className="flex gap-2">
                                <input type="number" min="0" placeholder="Mín" value={durationMin}
                                    onChange={e => { setDurationMin(e.target.value); setPage(1); }}
                                    className="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/30" />
                                <input type="number" min="0" placeholder="Máx" value={durationMax}
                                    onChange={e => { setDurationMax(e.target.value); setPage(1); }}
                                    className="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/30" />
                            </div>
                        </div>

                        {/* Distance & Gain */}
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <p className="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Distancia (km)</p>
                                <div className="flex gap-2">
                                    <input type="number" min="0" placeholder="Mín" value={distanceMin}
                                        onChange={e => { setDistanceMin(e.target.value); setPage(1); }}
                                        className="w-full px-2 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/30" />
                                    <input type="number" min="0" placeholder="Máx" value={distanceMax}
                                        onChange={e => { setDistanceMax(e.target.value); setPage(1); }}
                                        className="w-full px-2 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/30" />
                                </div>
                            </div>
                            <div>
                                <p className="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Desnivel (m)</p>
                                <div className="flex gap-2">
                                    <input type="number" min="0" placeholder="Mín" value={gainMin}
                                        onChange={e => { setGainMin(e.target.value); setPage(1); }}
                                        className="w-full px-2 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/30" />
                                    <input type="number" min="0" placeholder="Máx" value={gainMax}
                                        onChange={e => { setGainMax(e.target.value); setPage(1); }}
                                        className="w-full px-2 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/30" />
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {/* Content */}
                {isLoading && (
                    <div className="flex flex-col gap-3">
                        {[1, 2, 3].map(i => (
                            <div key={i} className="h-24 bg-slate-100 dark:bg-slate-800 rounded-xl animate-pulse" />
                        ))}
                    </div>
                )}

                {isError && (
                    <div className="text-center py-12 text-red-500">
                        <span className="material-symbols-outlined !text-4xl mb-2">error</span>
                        <p className="font-semibold">Error al cargar las rutas</p>
                    </div>
                )}

                {!isLoading && !isError && filtered.length === 0 && (
                    <div className="text-center py-16">
                        <span className="material-symbols-outlined !text-5xl text-slate-300 dark:text-slate-600 mb-3 block">route</span>
                        <p className="font-bold text-slate-500 dark:text-slate-400">
                            {routes.length === 0 ? "Todavía no has subido ninguna ruta" : "No hay rutas que coincidan con los filtros"}
                        </p>
                        {routes.length === 0 ? (
                            <button
                                onClick={() => navigate("/routes/new")}
                                className="mt-4 px-5 py-2.5 bg-primary text-white text-sm font-bold rounded-xl shadow-lg shadow-primary/20"
                            >
                                Subir primera ruta
                            </button>
                        ) : (
                            <button onClick={resetFilters} className="mt-4 px-5 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-sm font-bold rounded-xl">
                                Limpiar filtros
                            </button>
                        )}
                    </div>
                )}

                {!isLoading && !isError && filtered.length > 0 && (
                    <>
                        <div className="flex flex-col gap-3">
                            {paginated.map(route => (
                                <RouteRow
                                    key={route.id}
                                    route={route}
                                    sports={sports}
                                    isEditing={editingId === route.id}
                                    isSaving={updateMut.isPending && updateMut.variables?.id === route.id}
                                    onToggleEdit={() => setEditingId(id => id === route.id ? null : route.id)}
                                    onPatch={(patch) => updateMut.mutate({ id: route.id, patch })}
                                    onView={() => navigate(`/route/${route.slug}`)}
                                />
                            ))}
                        </div>

                        {/* Pagination */}
                        {totalPages > 1 && (
                            <div className="flex items-center justify-between mt-6 pt-4 border-t border-slate-200 dark:border-slate-700">
                                <button
                                    onClick={() => setPage(p => Math.max(1, p - 1))}
                                    disabled={safePage === 1}
                                    className="flex items-center gap-1.5 px-4 py-2 text-sm font-bold rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-30 disabled:cursor-not-allowed transition-all"
                                >
                                    <span className="material-symbols-outlined !text-base">chevron_left</span>
                                    Anterior
                                </button>
                                <div className="flex items-center gap-1.5">
                                    {Array.from({ length: totalPages }, (_, i) => i + 1).map(p => (
                                        <button
                                            key={p}
                                            onClick={() => setPage(p)}
                                            className={`w-8 h-8 rounded-lg text-sm font-bold transition-all ${p === safePage
                                                ? "bg-primary text-white shadow-sm shadow-primary/30"
                                                : "text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800"
                                                }`}
                                        >
                                            {p}
                                        </button>
                                    ))}
                                </div>
                                <button
                                    onClick={() => setPage(p => Math.min(totalPages, p + 1))}
                                    disabled={safePage === totalPages}
                                    className="flex items-center gap-1.5 px-4 py-2 text-sm font-bold rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-30 disabled:cursor-not-allowed transition-all"
                                >
                                    Siguiente
                                    <span className="material-symbols-outlined !text-base">chevron_right</span>
                                </button>
                            </div>
                        )}
                    </>
                )}
            </div>
        </>
    );
}

function RouteRow({
    route,
    sports,
    isEditing,
    isSaving,
    onToggleEdit,
    onPatch,
    onView,
}: {
    route: MyRouteItem;
    sports: SportOption[];
    isEditing: boolean;
    isSaving: boolean;
    onToggleEdit: () => void;
    onPatch: (patch: { title?: string; description?: string | null; sportCode?: string; visibility?: "PUBLIC" | "UNLISTED" | "PRIVATE"; status?: "DRAFT" | "PUBLISHED" | "ARCHIVED"; difficulty?: "EASY" | "MODERATE" | "HARD" | "EXPERT" | null; routeType?: "CIRCULAR" | "LINEAR" | "ROUND_TRIP" | null }) => void;
    onView: () => void;
}) {
    const [editTab, setEditTab] = useState<"actions" | "data">("actions");

    // Local form state
    const [formTitle, setFormTitle] = useState(route.title);
    const [formDesc, setFormDesc] = useState(route.description ?? "");
    const [formSport, setFormSport] = useState(route.sportCode ?? "");
    const [formDifficulty, setFormDifficulty] = useState<DifficultyKey | "">(route.difficulty as DifficultyKey ?? "");
    const [formRouteType, setFormRouteType] = useState<RouteTypeKey | "">(route.routeType as RouteTypeKey ?? "");

    const handleToggleEdit = () => {
        // Reset form to current route values when opening
        setFormTitle(route.title);
        setFormDesc(route.description ?? "");
        setFormSport(route.sportCode ?? "");
        setFormDifficulty(route.difficulty as DifficultyKey ?? "");
        setFormRouteType(route.routeType as RouteTypeKey ?? "");
        setEditTab("actions");
        onToggleEdit();
    };

    const handleSaveData = () => {
        onPatch({
            title: formTitle.trim() || undefined,
            description: formDesc.trim() || null,
            sportCode: formSport || undefined,
            difficulty: (formDifficulty as "EASY" | "MODERATE" | "HARD" | "EXPERT") || null,
            routeType: (formRouteType as "CIRCULAR" | "LINEAR" | "ROUND_TRIP") || null,
        });
        onToggleEdit();
    };

    const canSave = formTitle.trim() !== "";

    const km = (route.distanceM / 1000).toFixed(1);
    const vis = VISIBILITY_CONFIG[route.visibility];
    const st = STATUS_CONFIG[route.status as keyof typeof STATUS_CONFIG] ?? STATUS_CONFIG.DRAFT;
    const sportIcon = route.sportCode ? (SPORT_ICONS[route.sportCode] ?? "route") : "route";

    return (
        <div className="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden transition-all hover:shadow-md">
            <div className="flex items-stretch">
                {/* Thumbnail */}
                <div
                    className="w-20 sm:w-28 shrink-0 bg-slate-100 dark:bg-slate-800 cursor-pointer overflow-hidden"
                    onClick={onView}
                >
                    <img
                        src={route.image || getFallbackImage(route.id, "route")}
                        alt={route.title}
                        className="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                    />
                </div>

                {/* Main info */}
                <div className="flex-1 min-w-0 p-3 sm:p-4">
                    <div className="flex items-start justify-between gap-2">
                        <div className="min-w-0 flex-1">
                            <button
                                onClick={onView}
                                className="text-sm sm:text-base font-bold text-slate-900 dark:text-white hover:text-primary transition-colors text-left truncate block max-w-full"
                            >
                                {route.title}
                            </button>
                            <div className="flex items-center gap-2 mt-1 flex-wrap">
                                <span className="flex items-center gap-1 text-[10px] font-bold uppercase tracking-widest text-primary bg-primary/10 px-2 py-0.5 rounded">
                                    <span className="material-symbols-outlined !text-[12px]">{sportIcon}</span>
                                    {route.sportCode ?? "—"}
                                </span>
                                <span className="text-[11px] font-semibold text-slate-400 flex items-center gap-1">
                                    <span className="material-symbols-outlined !text-[13px]">straighten</span>
                                    {km} km
                                </span>
                                <span className="text-[11px] font-semibold text-slate-400 flex items-center gap-1">
                                    <span className="material-symbols-outlined !text-[13px]">trending_up</span>
                                    +{route.elevationGainM} m
                                </span>
                                {route.durationSeconds != null && (
                                    <span className="text-[11px] font-semibold text-slate-400 flex items-center gap-1">
                                        <span className="material-symbols-outlined !text-[13px]">schedule</span>
                                        {formatDuration(route.durationSeconds)}
                                    </span>
                                )}
                                {route.difficulty && DIFFICULTY_CONFIG[route.difficulty as DifficultyKey] && (
                                    <span className={`flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded border ${DIFFICULTY_CONFIG[route.difficulty as DifficultyKey].bg} ${DIFFICULTY_CONFIG[route.difficulty as DifficultyKey].color}`}>
                                        {DIFFICULTY_CONFIG[route.difficulty as DifficultyKey].label}
                                    </span>
                                )}
                                {route.routeType && ROUTE_TYPE_CONFIG[route.routeType as RouteTypeKey] && (
                                    <span className={`flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded border ${ROUTE_TYPE_CONFIG[route.routeType as RouteTypeKey].bg} ${ROUTE_TYPE_CONFIG[route.routeType as RouteTypeKey].color}`}>
                                        <span className="material-symbols-outlined !text-[11px]">{ROUTE_TYPE_CONFIG[route.routeType as RouteTypeKey].icon}</span>
                                        {ROUTE_TYPE_CONFIG[route.routeType as RouteTypeKey].label}
                                    </span>
                                )}
                                <span className="text-[11px] text-slate-400">
                                    {new Date(route.createdAt).toLocaleDateString("es-ES", { day: "2-digit", month: "short", year: "numeric" })}
                                </span>
                            </div>
                        </div>

                        <button
                            onClick={handleToggleEdit}
                            className="shrink-0 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
                        >
                            <span className="material-symbols-outlined !text-base">
                                {isEditing ? "expand_less" : "edit"}
                            </span>
                        </button>
                    </div>

                    {/* Badges */}
                    <div className="flex items-center gap-2 mt-2">
                        <span className={`flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold border rounded-full ${vis.bg} ${vis.color}`}>
                            <span className="material-symbols-outlined !text-[11px]">{vis.icon}</span>
                            {vis.label}
                        </span>
                        <span className={`flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-widest ${st.color}`}>
                            <span className={`w-1.5 h-1.5 rounded-full ${st.dot}`} />
                            {st.label}
                        </span>
                    </div>
                </div>
            </div>

            {/* Edit panel */}
            {isEditing && (
                <div className="border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                    {/* Tabs */}
                    <div className="flex border-b border-slate-100 dark:border-slate-800">
                        {(["actions", "data"] as const).map(tab => (
                            <button
                                key={tab}
                                onClick={() => setEditTab(tab)}
                                className={`flex items-center gap-1.5 px-4 py-2.5 text-[11px] font-black uppercase tracking-widest border-b-2 transition-all ${editTab === tab
                                    ? "border-primary text-primary"
                                    : "border-transparent text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                                    }`}
                            >
                                <span className="material-symbols-outlined !text-sm">
                                    {tab === "actions" ? "toggle_on" : "edit_note"}
                                </span>
                                {tab === "actions" ? "Acciones" : "Datos"}
                            </button>
                        ))}
                        <button
                            onClick={onView}
                            className="ml-auto flex items-center gap-1 px-4 py-2.5 text-[11px] font-bold text-primary hover:bg-primary/5 transition-all"
                        >
                            <span className="material-symbols-outlined !text-sm">open_in_new</span>
                            Ver
                        </button>
                    </div>

                    {/* Acciones tab */}
                    {editTab === "actions" && (
                        <div className="flex flex-wrap gap-4 items-start px-4 py-3">
                            {/* Visibility */}
                            <div className="flex flex-col gap-1">
                                <label className="text-[10px] font-black uppercase tracking-widest text-slate-400">Visibilidad</label>
                                <div className="flex gap-1.5">
                                    {(["PUBLIC", "UNLISTED", "PRIVATE"] as const).map(v => {
                                        const cfg = VISIBILITY_CONFIG[v];
                                        return (
                                            <button
                                                key={v}
                                                onClick={() => onPatch({ visibility: v })}
                                                className={`flex items-center gap-1 px-2.5 py-1.5 text-[11px] font-bold border rounded-lg transition-all ${route.visibility === v
                                                    ? `${cfg.bg} ${cfg.color} shadow-sm`
                                                    : "bg-white dark:bg-slate-900 text-slate-400 border-slate-200 dark:border-slate-700 hover:border-slate-300"
                                                    }`}
                                            >
                                                <span className="material-symbols-outlined !text-[13px]">{cfg.icon}</span>
                                                {cfg.label}
                                            </button>
                                        );
                                    })}
                                </div>
                            </div>

                            {/* Status */}
                            <div className="flex flex-col gap-1">
                                <label className="text-[10px] font-black uppercase tracking-widest text-slate-400">Estado</label>
                                <div className="flex gap-1.5">
                                    {(["PUBLISHED", "DRAFT", "ARCHIVED"] as const).map(s => {
                                        const cfg = STATUS_CONFIG[s];
                                        return (
                                            <button
                                                key={s}
                                                onClick={() => onPatch({ status: s })}
                                                className={`flex items-center gap-1.5 px-2.5 py-1.5 text-[11px] font-bold border rounded-lg transition-all ${route.status === s
                                                    ? "bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-800 shadow-sm"
                                                    : "bg-white dark:bg-slate-900 text-slate-400 border-slate-200 dark:border-slate-700 hover:border-slate-300"
                                                    }`}
                                            >
                                                <span className={`w-1.5 h-1.5 rounded-full ${cfg.dot}`} />
                                                {cfg.label}
                                            </button>
                                        );
                                    })}
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Datos tab */}
                    {editTab === "data" && (
                        <div className="px-4 py-4 flex flex-col gap-3">
                            {/* Title */}
                            <div>
                                <label className="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Título *</label>
                                <input
                                    type="text"
                                    value={formTitle}
                                    onChange={e => setFormTitle(e.target.value)}
                                    maxLength={120}
                                    className="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                                />
                            </div>

                            {/* Description */}
                            <div>
                                <label className="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Descripción</label>
                                <textarea
                                    value={formDesc}
                                    onChange={e => setFormDesc(e.target.value)}
                                    rows={3}
                                    maxLength={2000}
                                    placeholder="Describe la ruta, puntos de interés, dificultad..."
                                    className="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all resize-none"
                                />
                            </div>

                            {/* Sport */}
                            {sports.length > 0 && (
                                <div>
                                    <label className="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Deporte</label>
                                    <div className="flex flex-wrap gap-1.5">
                                        {sports.map(s => (
                                            <button
                                                key={s.code}
                                                onClick={() => setFormSport(s.code)}
                                                className={`flex items-center gap-1 px-2.5 py-1.5 text-[11px] font-bold border rounded-lg transition-all ${formSport === s.code
                                                    ? "bg-primary/10 border-primary/40 text-primary shadow-sm"
                                                    : "bg-white dark:bg-slate-900 text-slate-400 border-slate-200 dark:border-slate-700 hover:border-slate-300"
                                                    }`}
                                            >
                                                <span className="material-symbols-outlined !text-[13px]">
                                                    {SPORT_ICONS[s.code] ?? "route"}
                                                </span>
                                                {s.name}
                                            </button>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {/* Difficulty */}
                            <div>
                                <label className="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Dificultad</label>
                                <div className="flex flex-wrap gap-1.5">
                                    {(Object.entries(DIFFICULTY_CONFIG) as [DifficultyKey, typeof DIFFICULTY_CONFIG[DifficultyKey]][]).map(([key, cfg]) => (
                                        <button
                                            key={key}
                                            onClick={() => setFormDifficulty(formDifficulty === key ? "" : key)}
                                            className={`flex items-center gap-1 px-2.5 py-1.5 text-[11px] font-bold border rounded-lg transition-all ${formDifficulty === key
                                                ? `${cfg.bg} ${cfg.color} shadow-sm`
                                                : "bg-white dark:bg-slate-900 text-slate-400 border-slate-200 dark:border-slate-700 hover:border-slate-300"
                                                }`}
                                        >
                                            <span className="material-symbols-outlined !text-[13px]">{cfg.icon}</span>
                                            {cfg.label}
                                        </button>
                                    ))}
                                </div>
                            </div>

                            {/* Tipo de ruta */}
                            <div>
                                <label className="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Tipo de ruta</label>
                                <div className="flex flex-wrap gap-1.5">
                                    {(Object.entries(ROUTE_TYPE_CONFIG) as [RouteTypeKey, typeof ROUTE_TYPE_CONFIG[RouteTypeKey]][]).map(([key, cfg]) => (
                                        <button
                                            key={key}
                                            onClick={() => setFormRouteType(formRouteType === key ? "" : key)}
                                            className={`flex items-center gap-1 px-2.5 py-1.5 text-[11px] font-bold border rounded-lg transition-all ${formRouteType === key
                                                ? `${cfg.bg} ${cfg.color} shadow-sm`
                                                : "bg-white dark:bg-slate-900 text-slate-400 border-slate-200 dark:border-slate-700 hover:border-slate-300"
                                                }`}
                                        >
                                            <span className="material-symbols-outlined !text-[13px]">{cfg.icon}</span>
                                            {cfg.label}
                                        </button>
                                    ))}
                                </div>
                            </div>

                            {/* Save / Cancel */}
                            <div className="flex gap-2 pt-1">
                                <button
                                    onClick={handleToggleEdit}
                                    className="flex-1 py-2 text-[12px] font-bold text-slate-500 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-all"
                                >
                                    Cancelar
                                </button>
                                <button
                                    disabled={!canSave || isSaving}
                                    onClick={handleSaveData}
                                    className="flex-1 py-2 text-[12px] font-bold text-white bg-primary hover:bg-blue-600 disabled:opacity-40 disabled:cursor-not-allowed rounded-lg shadow-sm shadow-primary/20 transition-all"
                                >
                                    {isSaving ? "Guardando..." : "Guardar cambios"}
                                </button>
                            </div>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
