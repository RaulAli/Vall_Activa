import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { completeAthletePointMission, getAthletePointMissions, getAthletePointsSummary } from "../api/pointsApi";
import { getMe } from "../../user/api/userApi";
import { useAuthStore } from "../../../store/authStore";

export function AthletePointsCard() {
    const qc = useQueryClient();
    const { token, setUser } = useAuthStore();

    const summaryQuery = useQuery({
        queryKey: ["athlete", "points", "summary"],
        queryFn: () => getAthletePointsSummary(token!),
        enabled: !!token,
        staleTime: 10_000,
    });

    const missionsQuery = useQuery({
        queryKey: ["athlete", "points", "missions"],
        queryFn: () => getAthletePointMissions(token!),
        enabled: !!token,
        staleTime: 10_000,
    });

    const completeMutation = useMutation({
        mutationFn: (missionId: string) => completeAthletePointMission(token!, missionId),
        onSuccess: async () => {
            qc.invalidateQueries({ queryKey: ["athlete", "points", "summary"] });
            qc.invalidateQueries({ queryKey: ["athlete", "points", "missions"] });
            if (token) {
                const me = await getMe(token);
                setUser(me);
            }
        },
    });

    const summary = summaryQuery.data;
    const missions = missionsQuery.data ?? [];

    return (
        <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 space-y-5">
            <div className="flex items-start justify-between gap-4">
                <div>
                    <h3 className="text-sm font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Puntos</h3>
                    <p className="text-2xl font-black text-slate-900 dark:text-white mt-1">
                        {summary ? `${summary.balance} VAC` : "..."}
                    </p>
                </div>
                {summary && (
                    <span className={`text-[11px] font-black px-2.5 py-1 rounded-full ${summary.isVipForPoints
                        ? "bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300"
                        : "bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                        }`}>
                        {summary.isVipForPoints ? `VIP x${summary.vipMultiplier}` : "Normal x1"}
                    </span>
                )}
            </div>

            {summary && (
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div className="rounded-xl bg-slate-50 dark:bg-slate-800/60 p-3">
                        <p className="text-[11px] font-bold uppercase tracking-wider text-slate-400">Hoy</p>
                        <p className="text-lg font-black text-slate-900 dark:text-white">{summary.todayEarned}</p>
                    </div>
                    <div className="rounded-xl bg-slate-50 dark:bg-slate-800/60 p-3">
                        <p className="text-[11px] font-bold uppercase tracking-wider text-slate-400">Limite diario</p>
                        <p className="text-lg font-black text-slate-900 dark:text-white">{summary.dailyCap}</p>
                    </div>
                    <div className="rounded-xl bg-slate-50 dark:bg-slate-800/60 p-3">
                        <p className="text-[11px] font-bold uppercase tracking-wider text-slate-400">Puntos por km</p>
                        <p className="text-lg font-black text-slate-900 dark:text-white">{summary.pointsPerKm}</p>
                    </div>
                </div>
            )}

            <div>
                <p className="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">Misiones diarias</p>
                <div className="space-y-2">
                    {missionsQuery.isLoading && <p className="text-sm text-slate-400">Cargando misiones...</p>}
                    {!missionsQuery.isLoading && missions.length === 0 && (
                        <p className="text-sm text-slate-400">No hay misiones activas ahora mismo.</p>
                    )}
                    {missions.map((mission) => (
                        <div key={mission.id} className="rounded-xl border border-slate-200 dark:border-slate-700 p-3 flex items-center justify-between gap-3">
                            <div>
                                <p className="text-sm font-bold text-slate-900 dark:text-white">{mission.title}</p>
                                {mission.description && <p className="text-xs text-slate-500 dark:text-slate-400">{mission.description}</p>}
                                {mission.progress && (
                                    <p className="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                        Progreso: {mission.progress.current}/{mission.progress.target} {mission.progress.unit === "KM" ? "km" : "rutas"}
                                    </p>
                                )}
                            </div>
                            <div className="flex items-center gap-2">
                                <span className="text-xs font-black text-primary bg-primary/10 px-2 py-1 rounded-lg">+{mission.pointsReward} VAC</span>
                                {mission.auto ? (
                                    <span className="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                        {mission.completedToday ? "Completada" : "Automatica"}
                                    </span>
                                ) : (
                                    <button
                                        onClick={() => completeMutation.mutate(mission.id)}
                                        disabled={mission.completedToday || completeMutation.isPending}
                                        className="px-3 py-1.5 rounded-lg text-xs font-bold bg-primary text-white disabled:opacity-40"
                                    >
                                        {mission.completedToday ? "Completada" : "Completar"}
                                    </button>
                                )}
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}
