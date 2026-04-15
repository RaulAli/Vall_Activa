import { useEffect, useState } from "react";
import { useNavigate, useSearchParams } from "react-router-dom";
import { useMutation } from "@tanstack/react-query";
import { useAuthStore } from "../store/authStore";
import { VipStatusCard } from "../features/vip/ui/VipStatusCard";
import { confirmVipPayment, createVipCheckout, getMe, updateVipRenewal } from "../features/user/api/userApi";
import { HttpError } from "../shared/api/http";
import { Header } from "../widgets/layout/Header";

type Plan = {
    id: "MONTHLY" | "YEARLY";
    title: string;
    subtitle: string;
    price: string;
    cycle: string;
    savings?: string;
    features: string[];
    recommended?: boolean;
};

const plans: Plan[] = [
    {
        id: "MONTHLY",
        title: "VIP Mensual",
        subtitle: "Flexibilidad total",
        price: "10 EUR",
        cycle: "/ mes",
        features: [
            "Prioridad en reservas",
            "Acceso a actividades VIP",
            "Soporte prioritario",
            "Cancelacion en ventana ampliada",
        ],
    },
    {
        id: "YEARLY",
        title: "VIP Anual",
        subtitle: "La mejor opcion para atletas activos",
        price: "100 EUR",
        cycle: "/ ano",
        savings: "Ahorra 20 EUR vs mensual",
        features: [
            "Todo lo incluido en VIP mensual",
            "Ahorro anual inmediato",
            "Badge VIP destacado en tu perfil",
            "Promociones exclusivas para miembros anuales",
        ],
        recommended: true,
    },
];

export function PlansPage() {
    const navigate = useNavigate();
    const [searchParams, setSearchParams] = useSearchParams();
    const { user, token, isAuthenticated, setUser } = useAuthStore();
    const [actionError, setActionError] = useState<string | null>(null);
    const [actionSuccess, setActionSuccess] = useState<string | null>(null);
    const [processedSessionId, setProcessedSessionId] = useState<string | null>(null);

    useEffect(() => {
        if (!isAuthenticated) {
            navigate("/auth", { replace: true });
        }
    }, [isAuthenticated, navigate]);

    if (!user) return null;

    const isAthlete = user.role === "ROLE_ATHLETE";
    const hasActiveMonthlyVip = !!user.isVip
        && user.vipPlan === "MONTHLY"
        && !!user.vipExpiresAt
        && new Date(user.vipExpiresAt).getTime() > Date.now();

    const startCheckoutMut = useMutation({
        mutationFn: async (plan: "MONTHLY" | "YEARLY") => {
            if (!token) throw new Error("missing_token");
            return createVipCheckout(token, plan, window.location.origin);
        },
        onSuccess: (data) => {
            window.location.href = data.checkoutUrl;
        },
        onError: (err) => {
            setActionSuccess(null);
            if (err instanceof HttpError) {
                const code = (err.body as Record<string, unknown>).error as string | undefined;
                if (code === "forbidden_role") {
                    setActionError("Solo los atletas pueden activar un plan VIP.");
                    return;
                }
                if (code === "invalid_vip_plan") {
                    setActionError("El plan seleccionado no es valido.");
                    return;
                }
                if (code === "stripe_not_configured") {
                    setActionError("Stripe no esta configurado en entorno test.");
                    return;
                }
            }
            setActionError("No se pudo iniciar el pago en este momento.");
        },
    });

    const confirmPaymentMut = useMutation({
        mutationFn: async (sessionId: string) => {
            if (!token) throw new Error("missing_token");
            await confirmVipPayment(token, sessionId);
            const updated = await getMe(token);
            setUser(updated);
            return updated.vipPlan;
        },
        onSuccess: (vipPlan) => {
            setActionError(null);
            setActionSuccess(vipPlan === "YEARLY" ? "Pago confirmado. Plan anual VIP activado." : "Pago confirmado. Plan mensual VIP activado.");
            setSearchParams({}, { replace: true });
        },
        onError: (err) => {
            setActionSuccess(null);
            if (err instanceof HttpError) {
                const code = (err.body as Record<string, unknown>).error as string | undefined;
                if (code === "forbidden_role") {
                    setActionError("Solo los atletas pueden activar un plan VIP.");
                    return;
                }
                if (code === "invalid_vip_plan") {
                    setActionError("El plan seleccionado no es valido.");
                    return;
                }
                if (code === "payment_not_completed") {
                    setActionError("El pago aun no aparece como completado en Stripe.");
                    return;
                }
            }
            setActionError("No se pudo confirmar el pago en este momento.");
        },
    });

    const renewalMut = useMutation({
        mutationFn: async (cancelAtPeriodEnd: boolean) => {
            if (!token) throw new Error("missing_token");
            await updateVipRenewal(token, cancelAtPeriodEnd);
            const updated = await getMe(token);
            setUser(updated);
            return updated.vipCancelAtPeriodEnd;
        },
        onSuccess: (cancelAtPeriodEnd) => {
            setActionError(null);
            setActionSuccess(cancelAtPeriodEnd
                ? "Se ha cancelado la renovacion automatica para el siguiente periodo."
                : "Renovacion automatica reactivada correctamente.");
        },
        onError: () => {
            setActionSuccess(null);
            setActionError("No se pudo actualizar la renovacion VIP en este momento.");
        },
    });

    useEffect(() => {
        if (!token || !isAthlete) return;

        const checkout = searchParams.get("checkout");
        const sessionId = searchParams.get("session_id");

        if (checkout === "success" && sessionId && sessionId !== processedSessionId) {
            setProcessedSessionId(sessionId);
            confirmPaymentMut.mutate(sessionId);
            return;
        }

        if (checkout === "cancel") {
            setActionError("Pago cancelado.");
            setActionSuccess(null);
            setSearchParams({}, { replace: true });
        }
    }, [confirmPaymentMut, isAthlete, processedSessionId, searchParams, setSearchParams, token]);

    return (
        <div className="min-h-screen bg-slate-50 dark:bg-background-dark">
            <Header />
            <div className="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
                <div className="max-w-5xl mx-auto px-4 sm:px-6 py-4 flex items-center gap-3">
                    <button
                        onClick={() => navigate("/me")}
                        className="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-slate-500 dark:text-slate-400"
                    >
                        <span className="material-symbols-outlined">arrow_back</span>
                    </button>
                    <h1 className="text-lg font-black text-slate-900 dark:text-white">Planes VIP</h1>
                </div>
            </div>

            <div className="max-w-5xl mx-auto px-4 sm:px-6 py-8 space-y-6">
                <section className="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-800 bg-gradient-to-br from-blue-700 via-blue-800 to-slate-900 p-7 sm:p-10 text-white">
                    <div className="absolute -top-12 -right-12 w-48 h-48 rounded-full bg-cyan-300/20 blur-2xl" />
                    <div className="absolute -bottom-10 -left-6 w-44 h-44 rounded-full bg-blue-300/20 blur-2xl" />
                    <div className="relative">
                        <p className="text-[11px] uppercase tracking-wider font-black text-blue-100">Comparativa de planes</p>
                        <h2 className="text-3xl sm:text-4xl font-black mt-2 leading-tight">Elige tu nivel VIP para entrenar y reservar con ventaja</h2>
                        <p className="text-blue-100 mt-3 max-w-3xl text-sm sm:text-base font-medium">
                            Mantuvimos la experiencia visual de VAMO y añadimos una tabla clara para que el atleta entienda rapidamente coste y beneficios.
                        </p>
                        <div className="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-3 max-w-3xl">
                            <div className="rounded-xl bg-white/10 border border-white/20 px-4 py-3">
                                <p className="text-[10px] uppercase tracking-wider text-blue-100 font-black">Plan mensual</p>
                                <p className="text-xl font-black mt-1">10 EUR</p>
                            </div>
                            <div className="rounded-xl bg-white/10 border border-white/20 px-4 py-3">
                                <p className="text-[10px] uppercase tracking-wider text-blue-100 font-black">Plan anual</p>
                                <p className="text-xl font-black mt-1">100 EUR</p>
                            </div>
                            <div className="rounded-xl bg-white/10 border border-white/20 px-4 py-3">
                                <p className="text-[10px] uppercase tracking-wider text-blue-100 font-black">Ahorro anual</p>
                                <p className="text-xl font-black mt-1">20 EUR</p>
                            </div>
                        </div>
                    </div>
                </section>

                {isAthlete && <VipStatusCard user={user} />}

                {isAthlete && user.isVip && user.vipPlan && (
                    <section className="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <p className="text-xs uppercase tracking-wider font-black text-slate-500 dark:text-slate-400">Renovacion</p>
                            <p className="text-sm font-semibold text-slate-700 dark:text-slate-200 mt-1">
                                {user.vipCancelAtPeriodEnd
                                    ? "Tu plan finalizara al terminar el periodo actual."
                                    : "Tu plan se renovara automaticamente en el siguiente periodo."}
                            </p>
                        </div>
                        <button
                            type="button"
                            disabled={renewalMut.isPending}
                            onClick={() => {
                                setActionError(null);
                                setActionSuccess(null);
                                renewalMut.mutate(!user.vipCancelAtPeriodEnd);
                            }}
                            className={`h-10 px-4 rounded-xl text-sm font-black transition-colors ${user.vipCancelAtPeriodEnd
                                ? "bg-emerald-600 text-white hover:bg-emerald-500"
                                : "bg-rose-600 text-white hover:bg-rose-500"
                                }`}
                        >
                            {renewalMut.isPending
                                ? "Guardando..."
                                : (user.vipCancelAtPeriodEnd ? "Reactivar renovacion" : "Cancelar proxima renovacion")}
                        </button>
                    </section>
                )}

                {actionSuccess && (
                    <div className="rounded-2xl border border-emerald-200 dark:border-emerald-800/50 bg-emerald-50 dark:bg-emerald-950/20 p-4 text-sm text-emerald-700 dark:text-emerald-300 font-semibold flex items-start gap-2">
                        <span className="material-symbols-outlined !text-base mt-0.5">check_circle</span>
                        {actionSuccess}
                    </div>
                )}

                {actionError && (
                    <div className="rounded-2xl border border-red-200 dark:border-red-800/50 bg-red-50 dark:bg-red-950/20 p-4 text-sm text-red-700 dark:text-red-300 font-semibold flex items-start gap-2">
                        <span className="material-symbols-outlined !text-base mt-0.5">error</span>
                        {actionError}
                    </div>
                )}

                {!isAthlete && (
                    <div className="rounded-2xl border border-amber-200 dark:border-amber-800/50 bg-amber-50 dark:bg-amber-950/20 p-4 text-sm text-amber-800 dark:text-amber-200 font-semibold flex items-start gap-2">
                        <span className="material-symbols-outlined !text-base mt-0.5">info</span>
                        Los planes VIP estan orientados al rol atleta. Puedes revisar la comparativa, pero la activacion se habilita solo para cuentas atleta.
                    </div>
                )}

                <section className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    {plans.map((plan) => {
                        const isCurrentPlan = user.isVip && user.vipPlan === plan.id;
                        const isYearly = plan.id === "YEARLY";
                        const isYearlyUpgrade = isYearly && hasActiveMonthlyVip && !isCurrentPlan;
                        return (
                            <article
                                key={plan.id}
                                className={`rounded-2xl border bg-white dark:bg-slate-900 p-6 sm:p-7 shadow-sm relative overflow-hidden transition-all ${plan.recommended
                                    ? "border-primary/50 ring-2 ring-primary/20 hover:shadow-lg hover:-translate-y-0.5"
                                    : "border-slate-200 dark:border-slate-800 hover:shadow-md"
                                    }`}
                            >
                                {isYearly && <div className="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-emerald-400 to-cyan-400" />}
                                {plan.recommended && (
                                    <span className="absolute top-4 right-4 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black tracking-wider bg-primary text-white uppercase">
                                        <span className="material-symbols-outlined !text-xs">bolt</span>
                                        Recomendado
                                    </span>
                                )}
                                <p className="text-xs uppercase tracking-wider font-black text-slate-500 dark:text-slate-400">{plan.subtitle}</p>
                                <h3 className="text-2xl font-black text-slate-900 dark:text-white mt-1">{plan.title}</h3>
                                <div className="mt-4 flex items-end gap-1">
                                    <p className="text-4xl font-black text-slate-900 dark:text-white leading-none">{plan.price}</p>
                                    <p className="text-sm text-slate-500 dark:text-slate-400 font-semibold mb-1">{plan.cycle}</p>
                                </div>
                                {isYearlyUpgrade && (
                                    <p className="mt-2 text-sm font-black text-emerald-600 dark:text-emerald-400">Por solo 90 mas lo tienes</p>
                                )}
                                {plan.savings && (
                                    <p className="mt-2 text-sm font-bold text-emerald-600 dark:text-emerald-400">{plan.savings}</p>
                                )}

                                <ul className="mt-6 space-y-2.5">
                                    {plan.features.map((feature) => (
                                        <li key={feature} className="flex items-center gap-2.5 text-sm text-slate-700 dark:text-slate-200 font-medium">
                                            <span className="material-symbols-outlined !text-base text-emerald-600 dark:text-emerald-400">check_circle</span>
                                            {feature}
                                        </li>
                                    ))}
                                </ul>

                                <div className="mt-7">
                                    {isCurrentPlan ? (
                                        <button
                                            type="button"
                                            disabled
                                            className="w-full h-12 rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 font-black text-sm cursor-not-allowed"
                                        >
                                            Plan actual activo
                                        </button>
                                    ) : (
                                        <button
                                            type="button"
                                            disabled={!isAthlete || startCheckoutMut.isPending || confirmPaymentMut.isPending}
                                            onClick={() => {
                                                setActionError(null);
                                                setActionSuccess(null);
                                                startCheckoutMut.mutate(plan.id);
                                            }}
                                            className={`w-full h-12 rounded-xl font-black text-sm transition-colors ${isAthlete
                                                ? "bg-primary text-white hover:bg-primary/90"
                                                : "bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500 cursor-not-allowed"
                                                }`}
                                        >
                                            {isAthlete
                                                ? ((startCheckoutMut.isPending || confirmPaymentMut.isPending)
                                                    ? "Procesando..."
                                                    : "Seleccionar plan")
                                                : "Solo disponible para atletas"}
                                        </button>
                                    )}
                                </div>
                            </article>
                        );
                    })}
                </section>

                <section className="rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden bg-white dark:bg-slate-900">
                    <div className="px-5 sm:px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                        <h3 className="text-base sm:text-lg font-black text-slate-900 dark:text-white">Comparativa rapida</h3>
                        <p className="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Que incluye cada plan para que elijas en segundos.</p>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="min-w-full text-sm">
                            <thead>
                                <tr className="bg-slate-50 dark:bg-slate-800/60 text-left">
                                    <th className="px-5 py-3 font-black text-slate-700 dark:text-slate-200">Beneficio</th>
                                    <th className="px-5 py-3 font-black text-slate-700 dark:text-slate-200">Mensual</th>
                                    <th className="px-5 py-3 font-black text-slate-700 dark:text-slate-200">Anual</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                                {[
                                    ["Prioridad en reservas", "Si", "Si"],
                                    ["Actividades VIP", "Si", "Si"],
                                    ["Soporte prioritario", "Si", "Si"],
                                    ["Badge VIP destacado", "Basico", "Premium"],
                                    ["Ahorro", "-", "20 EUR"],
                                ].map((row) => (
                                    <tr key={row[0]} className="text-slate-700 dark:text-slate-200">
                                        <td className="px-5 py-3 font-semibold">{row[0]}</td>
                                        <td className="px-5 py-3">{row[1]}</td>
                                        <td className="px-5 py-3 font-bold text-emerald-700 dark:text-emerald-300">{row[2]}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    );
}
