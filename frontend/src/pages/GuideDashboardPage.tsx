import { useEffect, useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";
import { useMutation, useQuery } from "@tanstack/react-query";
import { Header } from "../widgets/layout/Header";
import { useAuthStore } from "../store/authStore";
import { getMyRoutes } from "../features/routes/api/routeApi";
import type { GuideAvailability, WeekdayKey } from "../features/guides/domain/types";
import { getMyGuideAvailability, updateMyGuideAvailability } from "../features/guides/api/guideApi";
import { getMyGuideBookings, updateMyGuideBookingStatus } from "../features/guides/api/guideBookingApi";
import { getMe, updateMe } from "../features/user/api/userApi";

const DAY_LABEL: Record<WeekdayKey, string> = {
    MONDAY: "Lunes",
    TUESDAY: "Martes",
    WEDNESDAY: "Miércoles",
    THURSDAY: "Jueves",
    FRIDAY: "Viernes",
    SATURDAY: "Sábado",
    SUNDAY: "Domingo",
};

const HOUR_SLOTS = Array.from({ length: 16 }, (_, i) => `${String(i + 6).padStart(2, "0")}:00`);

const BOOKING_STATUS_LABEL: Record<string, string> = {
    REQUESTED: "Pendiente",
    CONFIRMED: "Aceptada",
    COMPLETED: "Completada",
    REJECTED: "Rechazada",
    CANCELLED: "Cancelada",
};

const WEEKDAY_FROM_ENGLISH: Record<string, WeekdayKey> = {
    MONDAY: "MONDAY",
    TUESDAY: "TUESDAY",
    WEDNESDAY: "WEDNESDAY",
    THURSDAY: "THURSDAY",
    FRIDAY: "FRIDAY",
    SATURDAY: "SATURDAY",
    SUNDAY: "SUNDAY",
};

export function GuideDashboardPage() {
    const navigate = useNavigate();
    const { token, user, isAuthenticated, setUser } = useAuthStore();

    const [availability, setAvailability] = useState<GuideAvailability | null>(null);
    const [savedAt, setSavedAt] = useState<Date | null>(null);
    const [saveError, setSaveError] = useState<string | null>(null);
    const [guidePricePerHour, setGuidePricePerHour] = useState<string>(
        user?.guidePricePerHour != null ? user.guidePricePerHour.toFixed(2) : "25.00",
    );
    const [priceSaveMessage, setPriceSaveMessage] = useState<string | null>(null);

    useEffect(() => {
        if (!isAuthenticated) {
            navigate("/auth", { replace: true });
            return;
        }

        if (user?.role !== "ROLE_GUIDE") {
            navigate("/", { replace: true });
            return;
        }
    }, [isAuthenticated, navigate, user]);

    const { data: serverAvailability, isLoading: loadingAvailability, isError: loadAvailabilityError } = useQuery({
        queryKey: ["guide", "availability"],
        queryFn: () => getMyGuideAvailability(token!),
        enabled: !!token && user?.role === "ROLE_GUIDE",
    });

    useEffect(() => {
        if (!serverAvailability) return;
        setAvailability(serverAvailability);
        setSaveError(null);
    }, [serverAvailability]);

    useEffect(() => {
        if (loadAvailabilityError) {
            setSaveError("No se pudo cargar la disponibilidad del guide.");
        }
    }, [loadAvailabilityError]);

    useEffect(() => {
        if (user?.guidePricePerHour == null) return;
        setGuidePricePerHour(user.guidePricePerHour.toFixed(2));
    }, [user?.guidePricePerHour]);

    const { data: myRoutes = [] } = useQuery({
        queryKey: ["me", "routes"],
        queryFn: () => getMyRoutes(token!),
        enabled: !!token && user?.role === "ROLE_GUIDE",
    });

    const bookingsQuery = useQuery({
        queryKey: ["guide", "me", "bookings"],
        queryFn: () => getMyGuideBookings(token!),
        enabled: !!token && user?.role === "ROLE_GUIDE",
    });

    const updateBookingMut = useMutation({
        mutationFn: ({ id, status }: { id: string; status: "CONFIRMED" | "REJECTED" | "COMPLETED" }) =>
            updateMyGuideBookingStatus(token!, id, { status }),
        onSuccess: () => {
            bookingsQuery.refetch();
        },
    });

    const saveMut = useMutation({
        mutationFn: (next: GuideAvailability) => updateMyGuideAvailability(token!, next),
        onSuccess: (serverData) => {
            setAvailability(serverData);
            setSavedAt(new Date());
            setSaveError(null);
        },
        onError: () => {
            setSaveError("No se pudo guardar la disponibilidad. Reintenta.");
        },
    });

    const saveGuidePriceMut = useMutation({
        mutationFn: async (pricePerHour: number) => {
            await updateMe(token!, { guidePricePerHour: pricePerHour });
            return getMe(token!);
        },
        onSuccess: (updatedUser) => {
            setUser(updatedUser);
            setGuidePricePerHour(updatedUser.guidePricePerHour != null ? updatedUser.guidePricePerHour.toFixed(2) : guidePricePerHour);
            setPriceSaveMessage("Precio por hora guardado.");
            setTimeout(() => setPriceSaveMessage(null), 2500);
        },
        onError: () => {
            setPriceSaveMessage("No se pudo guardar el precio por hora.");
        },
    });

    const selectedHours = useMemo(() => {
        if (!availability) return 0;
        return availability.week.reduce((acc, d) => acc + d.slots.length, 0);
    }, [availability]);

    const activeDays = useMemo(() => {
        if (!availability) return 0;
        return availability.week.filter((d) => d.enabled).length;
    }, [availability]);

    const publishedRoutes = useMemo(() => myRoutes.filter((r) => r.status === "PUBLISHED").length, [myRoutes]);
    const pendingBookings = useMemo(
        () => (bookingsQuery.data ?? []).filter((item) => item.status === "REQUESTED").length,
        [bookingsQuery.data],
    );

    const slotStatusByWeekdayHour = useMemo(() => {
        const map = new Map<string, "REQUESTED" | "CONFIRMED">();
        const timezone = availability?.timezone ?? "UTC";

        for (const booking of bookingsQuery.data ?? []) {
            if (booking.status !== "REQUESTED" && booking.status !== "CONFIRMED") {
                continue;
            }

            const date = new Date(booking.scheduledFor);
            const dayName = new Intl.DateTimeFormat("en-US", {
                timeZone: timezone,
                weekday: "long",
            }).format(date).toUpperCase();

            const weekday = WEEKDAY_FROM_ENGLISH[dayName];
            if (!weekday) continue;

            const hour = new Intl.DateTimeFormat("en-GB", {
                timeZone: timezone,
                hour: "2-digit",
                minute: "2-digit",
                hour12: false,
            }).format(date);

            const key = `${weekday}|${hour}`;
            if (booking.status === "CONFIRMED") {
                map.set(key, "CONFIRMED");
                continue;
            }

            if (!map.has(key)) {
                map.set(key, "REQUESTED");
            }
        }

        return map;
    }, [availability?.timezone, bookingsQuery.data]);

    const persist = (next: GuideAvailability) => {
        if (!token) return;
        setAvailability(next);
        saveMut.mutate(next);
    };

    const toggleDayEnabled = (day: WeekdayKey) => {
        if (!availability) return;
        const next: GuideAvailability = {
            ...availability,
            week: availability.week.map((item) => {
                if (item.day !== day) return item;
                const enabled = !item.enabled;
                return {
                    ...item,
                    enabled,
                    slots: enabled ? (item.slots.length ? item.slots : ["09:00", "10:00"]) : [],
                };
            }),
        };
        persist(next);
    };

    const toggleSlot = (day: WeekdayKey, slot: string) => {
        if (!availability) return;

        const next: GuideAvailability = {
            ...availability,
            week: availability.week.map((item) => {
                if (item.day !== day) return item;
                if (!item.enabled) return item;

                const exists = item.slots.includes(slot);
                const slots = exists ? item.slots.filter((s) => s !== slot) : [...item.slots, slot].sort();
                return { ...item, slots };
            }),
        };

        persist(next);
    };

    const persistGuidePrice = () => {
        if (!token) return;
        const parsed = Number.parseFloat(guidePricePerHour);
        if (!Number.isFinite(parsed) || parsed <= 0) {
            setPriceSaveMessage("Introduce un precio/hora valido (> 0).");
            return;
        }
        saveGuidePriceMut.mutate(parsed);
    };

    if (!user || !isAuthenticated) return null;
    if (user.role !== "ROLE_GUIDE") return null;

    return (
        <>
            <Header />
            <div className="min-h-screen bg-slate-50 dark:bg-background-dark">
                <div className="max-w-6xl mx-auto px-4 sm:px-6 py-8">
                    <div className="flex items-center justify-between gap-4 mb-6">
                        <div>
                            <h1 className="text-2xl font-black text-slate-900 dark:text-white">Guide Dashboard</h1>
                            <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">Gestiona tu disponibilidad por horas y tus rutas publicadas.</p>
                        </div>
                        <div className="flex items-center gap-2">
                            <button
                                onClick={() => navigate("/routes/new")}
                                className="px-4 py-2.5 rounded-xl bg-primary text-white text-sm font-bold hover:bg-primary/90 transition-all"
                            >
                                Subir ruta
                            </button>
                            <button
                                onClick={() => navigate("/me/routes")}
                                className="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all"
                            >
                                Ver mis rutas
                            </button>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4">
                            <p className="text-xs font-black uppercase tracking-wider text-slate-400">Horas disponibles</p>
                            <p className="text-3xl font-black text-slate-900 dark:text-white mt-1">{selectedHours}h</p>
                        </div>
                        <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4">
                            <p className="text-xs font-black uppercase tracking-wider text-slate-400">Días activos</p>
                            <p className="text-3xl font-black text-slate-900 dark:text-white mt-1">{activeDays}</p>
                        </div>
                        <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4">
                            <p className="text-xs font-black uppercase tracking-wider text-slate-400">Rutas publicadas</p>
                            <p className="text-3xl font-black text-slate-900 dark:text-white mt-1">{publishedRoutes}</p>
                        </div>
                        <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 md:col-span-3">
                            <p className="text-xs font-black uppercase tracking-wider text-slate-400">Solicitudes pendientes</p>
                            <p className="text-3xl font-black text-slate-900 dark:text-white mt-1">{pendingBookings}</p>
                        </div>
                    </div>

                    <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 mb-6">
                        <p className="text-xs font-black uppercase tracking-wider text-slate-400 mb-2">Precio del guide</p>
                        <div className="flex flex-col sm:flex-row sm:items-center gap-3">
                            <label className="text-sm font-semibold text-slate-600 dark:text-slate-300">Precio por hora (EUR/h)</label>
                            <div className="flex items-center gap-2">
                                <input
                                    type="number"
                                    step="0.5"
                                    min="1"
                                    value={guidePricePerHour}
                                    onChange={(e) => setGuidePricePerHour(e.target.value)}
                                    onBlur={persistGuidePrice}
                                    className="w-36 h-10 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 text-sm font-bold text-slate-900 dark:text-white"
                                />
                                <button
                                    onClick={persistGuidePrice}
                                    disabled={saveGuidePriceMut.isPending}
                                    className="px-3 py-2 rounded-lg bg-primary text-white text-xs font-bold disabled:opacity-60"
                                >
                                    {saveGuidePriceMut.isPending ? "Guardando..." : "Guardar"}
                                </button>
                            </div>
                        </div>
                        {priceSaveMessage && <p className="text-[11px] mt-2 text-slate-500 dark:text-slate-400">{priceSaveMessage}</p>}
                    </div>

                    <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
                        <div className="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
                            <h2 className="text-base font-black text-slate-900 dark:text-white">Calendario semanal</h2>
                            <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">Selecciona las horas en las que aceptas reservas para cada día.</p>
                            <p className="text-[11px] text-slate-400 mt-1">Zona horaria: {availability?.timezone ?? "UTC"}</p>
                            <div className="mt-2 flex flex-wrap items-center gap-3 text-[11px] text-slate-500">
                                <span className="inline-flex items-center gap-1.5"><span className="w-2.5 h-2.5 rounded-full bg-primary" />Libre</span>
                                <span className="inline-flex items-center gap-1.5"><span className="w-2.5 h-2.5 rounded-full bg-amber-400" />Pending</span>
                                <span className="inline-flex items-center gap-1.5"><span className="w-2.5 h-2.5 rounded-full bg-emerald-500" />Ocupado</span>
                            </div>
                            {savedAt && <p className="text-[11px] text-emerald-600 dark:text-emerald-400 mt-1">Guardado automático: {savedAt.toLocaleTimeString()}</p>}
                            {saveMut.isPending && <p className="text-[11px] text-slate-500 mt-1">Guardando cambios...</p>}
                            {saveError && <p className="text-[11px] text-red-500 mt-1">{saveError}</p>}
                        </div>

                        <div className="overflow-x-auto">
                            {loadingAvailability && !availability ? (
                                <div className="p-8 flex justify-center">
                                    <span className="w-6 h-6 border-2 border-primary/30 border-t-primary rounded-full animate-spin" />
                                </div>
                            ) : (
                                <table className="min-w-[960px] w-full text-sm">
                                    <thead className="bg-slate-50 dark:bg-slate-800/50">
                                        <tr>
                                            <th className="text-left px-4 py-3 font-black text-slate-500 uppercase text-[11px] tracking-wider">Día</th>
                                            {HOUR_SLOTS.map((slot) => (
                                                <th key={slot} className="px-2 py-3 font-black text-slate-400 text-[11px] tracking-wider">{slot}</th>
                                            ))}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {availability?.week.map((dayRow) => (
                                            <tr key={dayRow.day} className="border-t border-slate-100 dark:border-slate-800">
                                                <td className="px-4 py-3">
                                                    <button
                                                        onClick={() => toggleDayEnabled(dayRow.day)}
                                                        className={`inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold border transition-all ${dayRow.enabled
                                                            ? "border-emerald-300 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-300"
                                                            : "border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-500"
                                                            }`}
                                                    >
                                                        <span className="material-symbols-outlined !text-sm">{dayRow.enabled ? "toggle_on" : "toggle_off"}</span>
                                                        {DAY_LABEL[dayRow.day]}
                                                    </button>
                                                </td>

                                                {HOUR_SLOTS.map((slot) => {
                                                    const selected = dayRow.slots.includes(slot);
                                                    const bookingStatus = slotStatusByWeekdayHour.get(`${dayRow.day}|${slot}`);
                                                    const colorClass = selected
                                                        ? bookingStatus === "CONFIRMED"
                                                            ? "bg-emerald-500 border-emerald-500"
                                                            : bookingStatus === "REQUESTED"
                                                                ? "bg-amber-400 border-amber-400"
                                                                : "bg-primary border-primary"
                                                        : "bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700";
                                                    return (
                                                        <td key={`${dayRow.day}-${slot}`} className="px-2 py-2 text-center">
                                                            <button
                                                                disabled={!dayRow.enabled}
                                                                onClick={() => toggleSlot(dayRow.day, slot)}
                                                                className={`w-7 h-7 rounded-md border transition-all ${colorClass} ${!dayRow.enabled ? "opacity-30 cursor-not-allowed" : "hover:scale-105"}`}
                                                                title={`${DAY_LABEL[dayRow.day]} ${slot}${bookingStatus === "REQUESTED" ? " · Pending" : bookingStatus === "CONFIRMED" ? " · Ocupado" : ""}`}
                                                            />
                                                        </td>
                                                    );
                                                })}
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            )}
                        </div>
                    </div>

                    <div className="mt-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
                        <div className="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
                            <h2 className="text-base font-black text-slate-900 dark:text-white">Reservas de atletas</h2>
                            <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">Acepta o rechaza solicitudes de rutas en tus horarios disponibles.</p>
                        </div>

                        {bookingsQuery.isLoading ? (
                            <div className="p-8 flex justify-center">
                                <span className="w-6 h-6 border-2 border-primary/30 border-t-primary rounded-full animate-spin" />
                            </div>
                        ) : (bookingsQuery.data ?? []).length === 0 ? (
                            <div className="p-6 text-sm text-slate-500 dark:text-slate-400">Todavía no tienes reservas.</div>
                        ) : (
                            <div className="divide-y divide-slate-100 dark:divide-slate-800">
                                {(bookingsQuery.data ?? []).map((booking) => {
                                    const when = new Date(booking.scheduledFor);
                                    const isPending = booking.status === "REQUESTED";
                                    const canComplete = booking.status === "CONFIRMED" && booking.paymentStatus === "PAID";

                                    return (
                                        <div key={booking.id} className="p-4 sm:p-5 flex flex-col gap-3">
                                            <div className="flex flex-wrap items-start justify-between gap-3">
                                                <div>
                                                    <p className="text-sm font-black text-slate-900 dark:text-white">
                                                        {booking.routeTitle ?? "Ruta"}
                                                    </p>
                                                    <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                                        {booking.athleteName ?? "Atleta"} · {when.toLocaleDateString()} {when.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}{booking.endsAt ? ` → ${new Date(booking.endsAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}` : ''}
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
                                                        {BOOKING_STATUS_LABEL[booking.status] ?? booking.status}
                                                    </span>
                                                </div>
                                            </div>

                                            {booking.notes && (
                                                <p className="text-xs text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 rounded-lg px-3 py-2">
                                                    “{booking.notes}”
                                                </p>
                                            )}

                                            {isPending && (
                                                <div className="flex gap-2 justify-end">
                                                    <button
                                                        onClick={() => updateBookingMut.mutate({ id: booking.id, status: "REJECTED" })}
                                                        disabled={updateBookingMut.isPending}
                                                        className="px-3 py-2 rounded-lg border border-rose-200 text-rose-700 dark:border-rose-800 dark:text-rose-300 text-xs font-bold"
                                                    >
                                                        Rechazar
                                                    </button>
                                                    <button
                                                        onClick={() => updateBookingMut.mutate({ id: booking.id, status: "CONFIRMED" })}
                                                        disabled={updateBookingMut.isPending}
                                                        className="px-3 py-2 rounded-lg bg-primary text-white text-xs font-bold"
                                                    >
                                                        Aceptar
                                                    </button>
                                                </div>
                                            )}

                                            {canComplete && (
                                                <div className="flex gap-2 justify-end">
                                                    <button
                                                        onClick={() => updateBookingMut.mutate({ id: booking.id, status: "COMPLETED" })}
                                                        disabled={updateBookingMut.isPending}
                                                        className="px-3 py-2 rounded-lg bg-indigo-600 text-white text-xs font-bold"
                                                    >
                                                        Marcar como completada
                                                    </button>
                                                </div>
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
