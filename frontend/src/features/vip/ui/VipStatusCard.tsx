import type { AuthUser } from "../../auth/domain/types";

type Benefit = {
    id: string;
    label: string;
    icon: string;
};

const BASE_BENEFITS: Benefit[] = [
    { id: "priority-booking", label: "Prioridad en reservas de actividades", icon: "event_available" },
    { id: "exclusive-routes", label: "Acceso a rutas y experiencias exclusivas", icon: "route" },
    { id: "support", label: "Soporte prioritario", icon: "support_agent" },
];

const YEARLY_EXTRA: Benefit = {
    id: "annual-savings",
    label: "Ahorro de 20 EUR al año frente al plan mensual",
    icon: "savings",
};

function planLabel(plan: AuthUser["vipPlan"]): string {
    if (plan === "YEARLY") return "Anual";
    if (plan === "MONTHLY") return "Mensual";
    return "Sin plan";
}

function formatDate(input?: string | null): string {
    if (!input) return "-";
    const d = new Date(input);
    if (Number.isNaN(d.getTime())) return "-";
    return d.toLocaleDateString("es-ES", { day: "2-digit", month: "short", year: "numeric" });
}

function daysLeft(input?: string | null): number | null {
    if (!input) return null;
    const end = new Date(input).getTime();
    if (Number.isNaN(end)) return null;
    const diff = end - Date.now();
    return Math.max(0, Math.ceil(diff / (1000 * 60 * 60 * 24)));
}

export function VipStatusCard({ user }: { user: AuthUser }) {
    const isVip = !!user.isVip;
    const benefits = user.vipPlan === "YEARLY" ? [...BASE_BENEFITS, YEARLY_EXTRA] : BASE_BENEFITS;
    const remaining = daysLeft(user.vipExpiresAt);

    return (
        <section className="rounded-2xl border border-amber-200/70 dark:border-amber-700/40 bg-gradient-to-br from-amber-50 via-orange-50 to-white dark:from-amber-950/40 dark:via-orange-950/25 dark:to-slate-900 shadow-sm overflow-hidden relative">
            <div className="absolute -top-8 -right-10 w-40 h-40 rounded-full bg-amber-300/20 blur-2xl pointer-events-none" />
            <div className="p-5 sm:p-6 border-b border-amber-200/70 dark:border-amber-700/30 relative">
                <div className="flex items-start justify-between gap-4">
                    <div>
                        <p className="text-[11px] font-black tracking-wider uppercase text-amber-700 dark:text-amber-300">Programa VIP</p>
                        <h3 className="text-lg sm:text-xl font-black text-slate-900 dark:text-white mt-1">Estado de suscripcion</h3>
                        <p className="text-sm text-slate-600 dark:text-slate-300 mt-1">
                            {isVip ? "Tu cuenta VIP esta activa y con beneficios disponibles." : "No tienes VIP activo. Activalo para desbloquear ventajas."}
                        </p>
                        {isVip && user.vipCancelAtPeriodEnd && (
                            <p className="text-xs font-bold text-rose-600 dark:text-rose-300 mt-2">
                                Renovacion cancelada: tu plan finalizara al terminar este periodo.
                            </p>
                        )}
                    </div>
                    <span
                        className={`inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-black ${isVip
                            ? "bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300"
                            : "bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                            }`}
                    >
                        <span className="material-symbols-outlined !text-sm">workspace_premium</span>
                        {isVip ? "VIP ACTIVO" : "VIP INACTIVO"}
                    </span>
                </div>
            </div>

            <div className="p-5 sm:p-6 grid grid-cols-1 sm:grid-cols-4 gap-3 sm:gap-4 relative">
                <div className="rounded-xl border border-slate-200/80 dark:border-slate-700 bg-white/80 dark:bg-slate-900/70 p-3">
                    <p className="text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 font-bold">Plan</p>
                    <p className="text-sm font-black text-slate-900 dark:text-white mt-1">{planLabel(user.vipPlan)}</p>
                </div>
                <div className="rounded-xl border border-slate-200/80 dark:border-slate-700 bg-white/80 dark:bg-slate-900/70 p-3">
                    <p className="text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 font-bold">Inicio</p>
                    <p className="text-sm font-black text-slate-900 dark:text-white mt-1">{formatDate(user.vipStartedAt)}</p>
                </div>
                <div className="rounded-xl border border-slate-200/80 dark:border-slate-700 bg-white/80 dark:bg-slate-900/70 p-3">
                    <p className="text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 font-bold">Renovacion</p>
                    <p className="text-sm font-black text-slate-900 dark:text-white mt-1">{formatDate(user.vipExpiresAt)}</p>
                </div>
                <div className="rounded-xl border border-slate-200/80 dark:border-slate-700 bg-white/80 dark:bg-slate-900/70 p-3">
                    <p className="text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 font-bold">Tiempo restante</p>
                    <p className="text-sm font-black text-slate-900 dark:text-white mt-1">
                        {isVip && remaining !== null ? `${remaining} dias` : "-"}
                    </p>
                </div>
            </div>

            <div className="px-5 sm:px-6 pb-5 sm:pb-6">
                <p className="text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 font-black mb-3">Beneficios activos</p>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    {benefits.map((benefit) => (
                        <div
                            key={benefit.id}
                            className={`flex items-center gap-2.5 rounded-xl border p-3 text-sm ${isVip
                                ? "border-emerald-200 bg-emerald-50/70 text-emerald-900 dark:border-emerald-800/50 dark:bg-emerald-950/20 dark:text-emerald-200"
                                : "border-slate-200 bg-slate-50 text-slate-500 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-300"
                                }`}
                        >
                            <span className="material-symbols-outlined !text-base">{isVip ? "check_circle" : benefit.icon}</span>
                            <span className="font-semibold">{benefit.label}</span>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
