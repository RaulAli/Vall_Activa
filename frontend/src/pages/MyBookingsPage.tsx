import { useEffect, useState } from "react";
import { useNavigate, useSearchParams } from "react-router-dom";
import { useMutation, useQuery } from "@tanstack/react-query";
import { Header } from "../widgets/layout/Header";
import { useAuthStore } from "../store/authStore";
import { HttpError } from "../shared/api/http";
import { confirmAthleteBookingPayment, createAthleteBookingCheckout, getMyAthleteBookings } from "../features/guides/api/guideBookingApi";

const STATUS_LABEL: Record<string, string> = {
    REQUESTED: "Pendiente",
    CONFIRMED: "Aceptada",
    COMPLETED: "Completada",
    REJECTED: "Rechazada",
    CANCELLED: "Cancelada",
};

export function MyBookingsPage() {
    const navigate = useNavigate();
    const [searchParams, setSearchParams] = useSearchParams();
    const { token, user, isAuthenticated } = useAuthStore();
    const [pageMessage, setPageMessage] = useState<string | null>(null);
    const [processedSessionId, setProcessedSessionId] = useState<string | null>(null);

    useEffect(() => {
        if (!isAuthenticated) {
            navigate("/auth", { replace: true });
            return;
        }

        if (user?.role !== "ROLE_ATHLETE") {
            navigate("/", { replace: true });
        }
    }, [isAuthenticated, navigate, user]);

    const bookingsQuery = useQuery({
        queryKey: ["athlete", "me", "bookings"],
        queryFn: () => getMyAthleteBookings(token!),
        enabled: !!token && user?.role === "ROLE_ATHLETE",
        refetchInterval: 15_000,
    });

    const checkoutMut = useMutation({
        mutationFn: (bookingId: string) => createAthleteBookingCheckout(token!, bookingId, window.location.origin),
        onSuccess: (res) => {
            window.location.href = res.checkoutUrl;
        },
        onError: (err) => {
            if (err instanceof HttpError) {
                const code = typeof err.body?.error === "string" ? err.body.error : "";
                if (code === "booking_not_confirmed") {
                    setPageMessage("Solo puedes pagar reservas confirmadas por el guía.");
                    return;
                }
            }
            setPageMessage("No se pudo iniciar el pago. Inténtalo de nuevo.");
        },
    });

    const confirmMut = useMutation({
        mutationFn: (sessionId: string) => confirmAthleteBookingPayment(token!, sessionId),
        onSuccess: () => {
            setPageMessage("Pago confirmado correctamente.");
            bookingsQuery.refetch();
            setSearchParams({}, { replace: true });
        },
        onError: () => {
            setPageMessage("No se pudo validar el pago todavía. Puedes refrescar en unos segundos.");
        },
    });

    useEffect(() => {
        if (!token || user?.role !== "ROLE_ATHLETE") return;
        const checkout = searchParams.get("checkout");
        const sessionId = searchParams.get("session_id");

        if (checkout === "success" && sessionId && sessionId !== processedSessionId) {
            setProcessedSessionId(sessionId);
            confirmMut.mutate(sessionId);
            return;
        }

        if (checkout === "cancel") {
            setPageMessage("Pago cancelado.");
            setSearchParams({}, { replace: true });
        }
    }, [confirmMut, processedSessionId, searchParams, setSearchParams, token, user?.role]);

    if (!isAuthenticated || user?.role !== "ROLE_ATHLETE") return null;

    return (
        <>
            <Header />
            <div className="min-h-screen bg-slate-50 dark:bg-background-dark">
                <div className="max-w-5xl mx-auto px-4 sm:px-6 py-8">
                    <div className="mb-6">
                        <h1 className="text-2xl font-black text-slate-900 dark:text-white">Mis reservas</h1>
                        <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">Consulta el estado de tus solicitudes con guías.</p>
                        {pageMessage && (
                            <p className="mt-3 text-sm text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2">
                                {pageMessage}
                            </p>
                        )}
                    </div>

                    <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
                        {bookingsQuery.isLoading ? (
                            <div className="p-8 flex justify-center">
                                <span className="w-6 h-6 border-2 border-primary/30 border-t-primary rounded-full animate-spin" />
                            </div>
                        ) : (bookingsQuery.data ?? []).length === 0 ? (
                            <div className="p-6 text-sm text-slate-500 dark:text-slate-400">Todavía no tienes reservas realizadas.</div>
                        ) : (
                            <div className="divide-y divide-slate-100 dark:divide-slate-800">
                                {(bookingsQuery.data ?? []).map((booking) => {
                                    const when = new Date(booking.scheduledFor);
                                    return (
                                        <div key={booking.id} className="p-4 sm:p-5 flex flex-col gap-3">
                                            <div className="flex flex-wrap items-start justify-between gap-3">
                                                <div>
                                                    <p className="text-sm font-black text-slate-900 dark:text-white">{booking.routeTitle ?? "Ruta"}</p>
                                                    <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                                        {when.toLocaleDateString()} · {when.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}{booking.endsAt ? ` → ${new Date(booking.endsAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}` : ''}
                                                    </p>
                                                </div>
                                                <div className="flex items-center gap-2">
                                                    {booking.paymentStatus === "PAID" && (
                                                        <span className="text-[11px] font-black px-2.5 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                                            Pagado
                                                        </span>
                                                    )}
                                                    <span className={`text-[11px] font-black px-2.5 py-1 rounded-full ${booking.status === "CONFIRMED"
                                                        ? "bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300"
                                                        : booking.status === "COMPLETED"
                                                            ? "bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300"
                                                            : booking.status === "REJECTED"
                                                                ? "bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300"
                                                                : "bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300"
                                                        }`}>
                                                        {STATUS_LABEL[booking.status] ?? booking.status}
                                                    </span>
                                                </div>
                                            </div>

                                            <div className="text-xs text-slate-500 dark:text-slate-400">
                                                {booking.paymentStatus === "PAID"
                                                    ? <>Pagado · {(booking.paymentAmountCents / 100).toFixed(2)} {booking.paymentCurrency.toUpperCase()}</>
                                                    : booking.paymentStatus === "PENDING"
                                                        ? "Pago iniciado — pendiente de confirmar"
                                                        : booking.status === "CONFIRMED"
                                                            ? <>Sin pagar · {(booking.paymentAmountCents / 100).toFixed(2)} {booking.paymentCurrency.toUpperCase()}</>
                                                            : null
                                                }
                                            </div>

                                            <div className="flex items-center justify-between gap-3">
                                                <div className="text-xs text-slate-600 dark:text-slate-300">
                                                    <span className="font-bold">Guía:</span> {booking.guideName ?? "Sin nombre"}
                                                    {booking.guideIsVerified ? " · Verificado" : ""}
                                                </div>
                                                <div className="flex gap-2">
                                                    {booking.status === "CONFIRMED" && booking.paymentStatus !== "PAID" && (
                                                        <button
                                                            onClick={() => checkoutMut.mutate(booking.id)}
                                                            disabled={checkoutMut.isPending}
                                                            className="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-bold disabled:opacity-60"
                                                        >
                                                            {checkoutMut.isPending ? "Procesando..." : booking.paymentStatus === "PENDING" ? "Continuar pago" : "Pagar"}
                                                        </button>
                                                    )}
                                                    {booking.guideSlug && (
                                                        <button
                                                            onClick={() => navigate(`/profile/${booking.guideSlug}`)}
                                                            className="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200"
                                                        >
                                                            Ver guía
                                                        </button>
                                                    )}
                                                    {booking.routeSlug && (
                                                        <button
                                                            onClick={() => navigate(`/route/${booking.routeSlug}`)}
                                                            className="px-3 py-1.5 rounded-lg bg-primary text-white text-xs font-bold"
                                                        >
                                                            Ver ruta
                                                        </button>
                                                    )}
                                                </div>
                                            </div>

                                            {booking.notes && (
                                                <p className="text-xs text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 rounded-lg px-3 py-2">“{booking.notes}”</p>
                                            )}
                                        </div>
                                    );
                                })}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}
