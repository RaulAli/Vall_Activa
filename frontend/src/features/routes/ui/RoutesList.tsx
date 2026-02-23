import { useNavigate } from "react-router-dom";
import { useShopStore } from "../../../store/shopStore";
import type { RouteListItem } from "../domain/types";
import { getFallbackImage } from "../../../shared/utils/images";

function formatDuration(seconds: number): string {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    if (h === 0) return `${m} min`;
    return m > 0 ? `${h}h ${m}min` : `${h}h`;
}

type Props = {
    items: RouteListItem[];
};

export function RoutesList({ items }: Props) {
    const navigate = useNavigate();
    const routes = useShopStore((s) => s.routes);
    const setRoutesSelected = useShopStore((s) => s.setRoutesSelected);
    const clearRoutesFocusBbox = useShopStore((s) => s.clearRoutesFocusBbox);

    return (
        <div className="flex flex-col gap-6">
            {/* Focus Mode Banner */}
            {routes.focusBbox && (
                <div className="p-4 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 flex justify-between items-center">
                    <div className="text-sm">
                        <span className="font-bold text-slate-900 dark:text-white">📌 Modo foco activo</span>
                        <p className="text-slate-500 dark:text-slate-400 text-xs">Mostrando {items.length} rutas en el área</p>
                    </div>
                    <button
                        onClick={() => clearRoutesFocusBbox()}
                        className="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg text-xs font-bold transition-colors"
                    >
                        Ver todas
                    </button>
                </div>
            )}

            {/* Routes Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6 pb-8">
                {items.map((r) => {
                    const isSelected = routes.selected?.slug === r.slug;

                    return (
                        <div
                            key={r.id}
                            className="group bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden hover:shadow-xl hover:shadow-slate-200/50 dark:hover:shadow-none transition-all cursor-pointer"
                            onClick={() => navigate(`/route/${r.slug}`)}
                        >
                            <div className="relative aspect-[16/9] overflow-hidden bg-slate-200 dark:bg-slate-800">
                                <img
                                    className="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 opacity-80 group-hover:opacity-100"
                                    src={r.image || getFallbackImage(r.id, "route")}
                                    alt={r.title}
                                />
                                <div className="absolute top-3 left-3 flex gap-2">
                                    <span className="px-2 py-1 bg-white/90 dark:bg-slate-900/90 backdrop-blur rounded text-[10px] font-bold uppercase tracking-widest text-primary">Moderate</span>
                                </div>
                                <div className="absolute top-3 right-3 flex gap-2">
                                    {/* Map Toggle (Only show if needed or simplify) */}
                                    {routes.focusBbox && (
                                        <button
                                            onClick={(e) => {
                                                e.stopPropagation();
                                                setRoutesSelected(isSelected ? null : { slug: r.slug, polyline: null });
                                            }}
                                            className={`size-8 flex items-center justify-center rounded-full backdrop-blur transition-all ${isSelected ? 'bg-primary text-white' : 'bg-white/20 text-white hover:bg-white/40'}`}
                                            title={isSelected ? "Quitar del mapa" : "Ver en mapa"}
                                        >
                                            <span className="material-symbols-outlined !text-xl">{isSelected ? "layers_clear" : "layers"}</span>
                                        </button>
                                    )}
                                    <button
                                        onClick={(e) => e.stopPropagation()}
                                        className="size-8 flex items-center justify-center rounded-full bg-white/20 backdrop-blur text-white hover:bg-white/40"
                                    >
                                        <span className="material-symbols-outlined !text-xl">bookmark</span>
                                    </button>
                                </div>
                            </div>
                            <div className="p-4">
                                <div className="flex justify-between items-start mb-2">
                                    <h4 className="font-bold text-slate-900 dark:text-white group-hover:text-primary transition-colors truncate pr-2">{r.title}</h4>
                                    <span className="font-extrabold text-primary text-sm tracking-tight">$5.00</span>
                                </div>
                                <div className="flex items-center gap-4 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                                    <div className="flex items-center gap-1">
                                        <span className="material-symbols-outlined !text-[14px]">distance</span>
                                        {(r.distanceM / 1000).toFixed(1)} km
                                    </div>
                                    {r.durationSeconds != null && (
                                        <div className="flex items-center gap-1">
                                            <span className="material-symbols-outlined !text-[14px]">schedule</span>
                                            {formatDuration(r.durationSeconds)}
                                        </div>
                                    )}
                                    <div className="flex items-center gap-1">
                                        <span className="material-symbols-outlined !text-[14px]">altitude</span>
                                        {r.elevationGainM}m
                                    </div>
                                </div>
                            </div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}
