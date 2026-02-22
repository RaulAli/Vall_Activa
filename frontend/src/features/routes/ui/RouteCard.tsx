import { useNavigate } from "react-router-dom";
import type { RouteListItem } from "../domain/types";
import { getFallbackImage } from "../../../shared/utils/images";

export function RouteCard({ item }: { item: RouteListItem }) {
    const navigate = useNavigate();
    const km = (item.distanceM / 1000).toFixed(1);

    return (
        <div
            onClick={() => navigate(`/route/${item.slug}`)}
            className="group bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden hover:shadow-xl hover:shadow-slate-200/50 dark:hover:shadow-none transition-all cursor-pointer"
        >
            <div className="relative aspect-[16/9] overflow-hidden bg-slate-100 dark:bg-slate-800">
                <img
                    className="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                    src={item.image || getFallbackImage(item.id, "route")}
                    alt={item.title}
                />
                <div className="absolute top-3 left-3">
                    <span className="px-2 py-1 bg-white/90 dark:bg-slate-900/90 backdrop-blur rounded text-[10px] font-bold uppercase tracking-widest text-primary shadow-sm">
                        {item.sportId}
                    </span>
                </div>
                <div className="absolute bottom-3 right-3 flex flex-col items-end gap-1">
                    <span className="px-2 py-1 bg-white/10 backdrop-blur-md text-white rounded text-[10px] font-bold shadow">
                        {km} km
                    </span>
                    <span className="px-2 py-1 bg-white/10 backdrop-blur-md text-white rounded text-[10px] font-bold shadow">
                        +{item.elevationGainM} m
                    </span>
                </div>
            </div>

            <div className="p-4">
                <div className="flex justify-between items-start mb-1">
                    <h4 className="font-bold text-slate-900 dark:text-white group-hover:text-primary transition-colors truncate pr-2">
                        {item.title}
                    </h4>
                </div>
                <div className="flex items-center justify-between text-[10px] font-bold uppercase tracking-tighter text-slate-400 mb-3">
                    <div className="flex items-center gap-1">
                        <span className="material-symbols-outlined !text-[14px]">calendar_month</span>
                        {new Date(item.createdAt).toLocaleDateString()}
                    </div>
                    <button className="text-primary hover:underline transition-all">Ver más</button>
                </div>
                {(item.creatorName || item.creatorAvatar) && (
                    <div className="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <div className="w-6 h-6 rounded-full bg-primary/10 overflow-hidden flex items-center justify-center shrink-0">
                            {item.creatorAvatar
                                ? <img src={item.creatorAvatar} alt={item.creatorName ?? ""} className="w-full h-full object-cover" />
                                : <span className="text-[9px] font-extrabold text-primary">{item.creatorName?.charAt(0)?.toUpperCase()}</span>
                            }
                        </div>
                        <span className="text-[10px] font-semibold text-slate-500 dark:text-slate-400 truncate">{item.creatorName}</span>
                    </div>
                )}
            </div>
        </div>
    );
}

