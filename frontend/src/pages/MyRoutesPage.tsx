import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { Header } from "../widgets/layout/Header";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { useAuthStore } from "../store/authStore";
import { getMyRoutes, updateRoute } from "../features/routes/api/routeApi";
import { getFallbackImage } from "../shared/utils/images";
import type { MyRouteItem } from "../features/routes/domain/types";

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
    PUBLIC:   { label: "Pública",   icon: "public",       color: "text-emerald-600 dark:text-emerald-400",  bg: "bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800" },
    UNLISTED: { label: "No listada", icon: "link",         color: "text-amber-600 dark:text-amber-400",      bg: "bg-amber-50 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800" },
    PRIVATE:  { label: "Privada",   icon: "lock",          color: "text-slate-500 dark:text-slate-400",      bg: "bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700" },
} as const;

const STATUS_CONFIG = {
    PUBLISHED: { label: "Publicada", color: "text-blue-600 dark:text-blue-400", dot: "bg-blue-500" },
    DRAFT:     { label: "Borrador",  color: "text-slate-400",                    dot: "bg-slate-400" },
    ARCHIVED:  { label: "Archivada", color: "text-slate-400",                    dot: "bg-slate-300" },
} as const;

type VisibilityFilter = "ALL" | "PUBLIC" | "UNLISTED" | "PRIVATE";

export function MyRoutesPage() {
    const navigate = useNavigate();
    const { token } = useAuthStore();
    const queryClient = useQueryClient();

    const [visFilter, setVisFilter] = useState<VisibilityFilter>("ALL");
    const [editingId, setEditingId] = useState<string | null>(null);

    const { data: routes = [], isLoading, isError } = useQuery({
        queryKey: ["me", "routes"],
        queryFn: () => getMyRoutes(token!),
        enabled: !!token,
    });

    const updateMut = useMutation({
        mutationFn: ({ id, patch }: {
            id: string;
            patch: { visibility?: "PUBLIC" | "UNLISTED" | "PRIVATE"; status?: "DRAFT" | "PUBLISHED" | "ARCHIVED" };
        }) => updateRoute(token!, id, patch),
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

    const filtered = visFilter === "ALL" ? routes : routes.filter(r => r.visibility === visFilter);

    const stats = {
        total:    routes.length,
        public:   routes.filter(r => r.visibility === "PUBLIC").length,
        unlisted: routes.filter(r => r.visibility === "UNLISTED").length,
        private:  routes.filter(r => r.visibility === "PRIVATE").length,
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
                    { key: "PUBLIC",   label: "Públicas",    count: stats.public,   icon: "public",   color: "text-emerald-600" },
                    { key: "UNLISTED", label: "No listadas", count: stats.unlisted, icon: "link",     color: "text-amber-600" },
                    { key: "PRIVATE",  label: "Privadas",    count: stats.private,  icon: "lock",     color: "text-slate-500" },
                ] as const).map(s => (
                    <button
                        key={s.key}
                        onClick={() => setVisFilter(v => v === s.key ? "ALL" : s.key)}
                        className={`flex flex-col items-center gap-1 p-3 rounded-xl border transition-all ${
                            visFilter === s.key
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

            {/* Filter chips */}
            <div className="flex gap-2 mb-4 flex-wrap">
                {(["ALL", "PUBLIC", "UNLISTED", "PRIVATE"] as VisibilityFilter[]).map(f => (
                    <button
                        key={f}
                        onClick={() => setVisFilter(f)}
                        className={`px-3 py-1.5 rounded-full text-xs font-bold border transition-all ${
                            visFilter === f
                                ? "bg-primary text-white border-primary shadow-sm"
                                : "bg-white dark:bg-slate-900 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:border-slate-300"
                        }`}
                    >
                        {f === "ALL" ? "Todas" : VISIBILITY_CONFIG[f].label}
                    </button>
                ))}
            </div>

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
                        {visFilter === "ALL" ? "Todavía no has subido ninguna ruta" : `No tienes rutas ${VISIBILITY_CONFIG[visFilter].label.toLowerCase()}s`}
                    </p>
                    {visFilter === "ALL" && (
                        <button
                            onClick={() => navigate("/routes/new")}
                            className="mt-4 px-5 py-2.5 bg-primary text-white text-sm font-bold rounded-xl shadow-lg shadow-primary/20"
                        >
                            Subir primera ruta
                        </button>
                    )}
                </div>
            )}

            {!isLoading && !isError && filtered.length > 0 && (
                <div className="flex flex-col gap-3">
                    {filtered.map(route => (
                        <RouteRow
                            key={route.id}
                            route={route}
                            isEditing={editingId === route.id}
                            onToggleEdit={() => setEditingId(id => id === route.id ? null : route.id)}
                            onVisibilityChange={(v) => updateMut.mutate({ id: route.id, patch: { visibility: v } })}
                            onStatusChange={(s) => updateMut.mutate({ id: route.id, patch: { status: s } })}
                            onView={() => navigate(`/route/${route.slug}`)}
                        />
                    ))}
                </div>
            )}
        </div>
        </>
    );
}

function RouteRow({
    route,
    isEditing,
    onToggleEdit,
    onVisibilityChange,
    onStatusChange,
    onView,
}: {
    route: MyRouteItem;
    isEditing: boolean;
    onToggleEdit: () => void;
    onVisibilityChange: (v: "PUBLIC" | "UNLISTED" | "PRIVATE") => void;
    onStatusChange: (s: "DRAFT" | "PUBLISHED" | "ARCHIVED") => void;
    onView: () => void;
}) {
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
                                {/* Sport chip */}
                                <span className="flex items-center gap-1 text-[10px] font-bold uppercase tracking-widest text-primary bg-primary/10 px-2 py-0.5 rounded">
                                    <span className="material-symbols-outlined !text-[12px]">{sportIcon}</span>
                                    {route.sportCode ?? "—"}
                                </span>
                                {/* Stats */}
                                <span className="text-[11px] font-semibold text-slate-400 flex items-center gap-1">
                                    <span className="material-symbols-outlined !text-[13px]">straighten</span>
                                    {km} km
                                </span>
                                <span className="text-[11px] font-semibold text-slate-400 flex items-center gap-1">
                                    <span className="material-symbols-outlined !text-[13px]">trending_up</span>
                                    +{route.elevationGainM} m
                                </span>
                                {/* Date */}
                                <span className="text-[11px] text-slate-400">
                                    {new Date(route.createdAt).toLocaleDateString("es-ES", { day: "2-digit", month: "short", year: "numeric" })}
                                </span>
                            </div>
                        </div>

                        {/* Edit toggle */}
                        <button
                            onClick={onToggleEdit}
                            className="shrink-0 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
                            title="Editar"
                        >
                            <span className="material-symbols-outlined !text-base">
                                {isEditing ? "expand_less" : "tune"}
                            </span>
                        </button>
                    </div>

                    {/* Badges row */}
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
                <div className="border-t border-slate-100 dark:border-slate-800 px-4 py-3 bg-slate-50 dark:bg-slate-800/50">
                    <div className="flex flex-wrap gap-4 items-center">
                        {/* Visibility select */}
                        <div className="flex flex-col gap-1">
                            <label className="text-[10px] font-black uppercase tracking-widest text-slate-400">Visibilidad</label>
                            <div className="flex gap-1.5">
                                {(["PUBLIC", "UNLISTED", "PRIVATE"] as const).map(v => {
                                    const cfg = VISIBILITY_CONFIG[v];
                                    const active = route.visibility === v;
                                    return (
                                        <button
                                            key={v}
                                            onClick={() => onVisibilityChange(v)}
                                            className={`flex items-center gap-1 px-2.5 py-1.5 text-[11px] font-bold border rounded-lg transition-all ${
                                                active
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

                        {/* Status select */}
                        <div className="flex flex-col gap-1">
                            <label className="text-[10px] font-black uppercase tracking-widest text-slate-400">Estado</label>
                            <div className="flex gap-1.5">
                                {(["PUBLISHED", "DRAFT"] as const).map(s => {
                                    const cfg = STATUS_CONFIG[s];
                                    const active = route.status === s;
                                    return (
                                        <button
                                            key={s}
                                            onClick={() => onStatusChange(s)}
                                            className={`flex items-center gap-1.5 px-2.5 py-1.5 text-[11px] font-bold border rounded-lg transition-all ${
                                                active
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

                        {/* View link */}
                        <button
                            onClick={onView}
                            className="ml-auto flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold text-primary hover:bg-primary/5 border border-primary/20 rounded-lg transition-all"
                        >
                            <span className="material-symbols-outlined !text-[13px]">open_in_new</span>
                            Ver ruta
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}
