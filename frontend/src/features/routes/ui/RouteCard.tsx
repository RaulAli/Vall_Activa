import { useNavigate } from "react-router-dom";
import type { RouteListItem } from "../domain/types";
import { getFallbackImage } from "../../../shared/utils/images";

function formatDuration(seconds: number): string {
  const h = Math.floor(seconds / 3600);
  const m = Math.floor((seconds % 3600) / 60);
  if (h === 0) return `${m} min`;
  return m > 0 ? `${h}h ${m}min` : `${h}h`;
}

const SPORT_LABELS: Record<string, string> = {
  HIKE: "Senderismo",
  BIKE: "Ciclismo",
  RUN: "Running",
  SKI: "Esqu\u00ed",
  CLIMB: "Escalada",
  KAYAK: "Kayak",
  SURF: "Surf",
  SWIM: "Nataci\u00f3n",
};

const DIFFICULTY_BADGE: Record<string, { label: string; cls: string }> = {
  EASY: { label: "Fácil", cls: "bg-emerald-500/90 text-white" },
  MODERATE: { label: "Moderada", cls: "bg-amber-500/90 text-white" },
  HARD: { label: "Difícil", cls: "bg-orange-500/90 text-white" },
  EXPERT: { label: "Experto", cls: "bg-red-600/90 text-white" },
};
const ROUTE_TYPE_BADGE: Record<string, { label: string; icon: string; cls: string }> = {
  CIRCULAR: { label: "Circular", icon: "loop", cls: "bg-white/90 dark:bg-slate-900/90 text-blue-600" },
  LINEAR: { label: "Lineal", icon: "trending_flat", cls: "bg-white/90 dark:bg-slate-900/90 text-amber-600" },
  ROUND_TRIP: { label: "Ida y vuelta", icon: "sync_alt", cls: "bg-white/90 dark:bg-slate-900/90 text-teal-600" },
};
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
        <div className="absolute top-3 left-3 flex flex-col gap-1">
          <span className="px-2 py-1 bg-white/90 dark:bg-slate-900/90 backdrop-blur rounded text-[10px] font-bold uppercase tracking-widest text-primary shadow-sm">
            {item.sportCode ? (SPORT_LABELS[item.sportCode] ?? item.sportCode) : null}
          </span>
          {item.difficulty && DIFFICULTY_BADGE[item.difficulty] && (
            <span className={`px-2 py-1 backdrop-blur rounded text-[10px] font-bold uppercase tracking-widest shadow-sm ${DIFFICULTY_BADGE[item.difficulty].cls}`}>
              {DIFFICULTY_BADGE[item.difficulty].label}
            </span>
          )}
          {item.routeType && ROUTE_TYPE_BADGE[item.routeType] && (
            <span className={`flex items-center gap-1 px-2 py-1 backdrop-blur rounded text-[10px] font-bold shadow-sm ${ROUTE_TYPE_BADGE[item.routeType].cls}`}>
              <span className="material-symbols-outlined !text-[12px]">{ROUTE_TYPE_BADGE[item.routeType].icon}</span>
              {ROUTE_TYPE_BADGE[item.routeType].label}
            </span>
          )}
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
          {item.durationSeconds != null && (
            <div className="flex items-center gap-1">
              <span className="material-symbols-outlined !text-[14px]">schedule</span>
              {formatDuration(item.durationSeconds)}
            </div>
          )}
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

