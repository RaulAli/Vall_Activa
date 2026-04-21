import { useEffect, useMemo, useState } from "react";
import { useMutation, useQuery } from "@tanstack/react-query";
import { useNavigate } from "react-router-dom";
import { Header } from "../widgets/layout/Header";
import { Footer } from "../widgets/layout/Footer";
import { Loader } from "../shared/ui/Loader";
import { useAuthStore } from "../store/authStore";
import { useOffersListQuery } from "../features/offers/queries/useOffersListQuery";
import { getMyOfferRedemptions, redeemOfferWithPoints } from "../features/offers/api/offerApi";
import { getMe } from "../features/user/api/userApi";
import { getFallbackImage } from "../shared/utils/images";
import { HttpError } from "../shared/api/http";

const WORLD_BBOX = { minLng: -180, minLat: -85, maxLng: 180, maxLat: 85 };

function buildQrUrl(payload: string): string {
    return `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(payload)}`;
}

export function MyCouponsPage() {
    const navigate = useNavigate();
    const { token, user, isAuthenticated, setUser } = useAuthStore();
    const [pageMessage, setPageMessage] = useState<string | null>(null);

    const offersQuery = useOffersListQuery({
        bbox: WORLD_BBOX,
        q: "",
        discountType: null,
        inStock: true,
        priceMin: null,
        priceMax: null,
        pointsMin: 1,
        pointsMax: null,
        sort: "recent",
        order: "desc",
        page: 1,
        limit: 12,
        enabled: isAuthenticated && user?.role === "ROLE_ATHLETE",
    });

    const redemptionsQuery = useQuery({
        queryKey: ["athlete", "offers", "redemptions"],
        queryFn: () => getMyOfferRedemptions(token!),
        enabled: !!token && isAuthenticated && user?.role === "ROLE_ATHLETE",
        staleTime: 10_000,
    });

    const redeemMutation = useMutation({
        mutationFn: (offerId: string) => redeemOfferWithPoints(token!, offerId),
        onSuccess: async () => {
            if (token) {
                const me = await getMe(token);
                setUser(me);
            }
            setPageMessage("Oferta canjeada. Ya tienes el cupón QR disponible abajo.");
            offersQuery.refetch();
            redemptionsQuery.refetch();
        },
        onError: (err) => {
            if (err instanceof HttpError) {
                const code = typeof err.body?.error === "string" ? err.body.error : "";
                if (code === "insufficient_points") {
                    setPageMessage("No tienes puntos VAC suficientes para este canje.");
                    return;
                }
                if (code === "out_of_stock") {
                    setPageMessage("Esta oferta se ha quedado sin stock.");
                    return;
                }
            }
            setPageMessage("No se pudo completar el canje en este momento.");
        },
    });

    const pointsBalance = user?.pointsBalance ?? 0;

    const offers = useMemo(() => {
        return (offersQuery.data?.items ?? []).filter((o) => o.pointsCost > 0);
    }, [offersQuery.data?.items]);

    useEffect(() => {
        if (!isAuthenticated) {
            navigate("/auth", { replace: true });
            return;
        }

        if (user?.role !== "ROLE_ATHLETE") {
            navigate("/", { replace: true });
        }
    }, [isAuthenticated, navigate, user?.role]);

    if (!isAuthenticated || user?.role !== "ROLE_ATHLETE") return null;

    return (
        <div className="min-h-screen bg-slate-50 dark:bg-background-dark text-slate-900 dark:text-white">
            <Header />

            <main className="max-w-[1200px] mx-auto px-4 md:px-8 py-8 space-y-8">
                <section className="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 md:p-6">
                    <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 className="text-2xl font-black">Mis cupones VAC</h1>
                            <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                Canjea ofertas con puntos VAC y enseña tu QR en el negocio.
                            </p>
                        </div>
                        <div className="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300 font-black">
                            <span className="material-symbols-outlined !text-[18px]">stars</span>
                            {pointsBalance} VAC
                        </div>
                    </div>
                    {pageMessage && (
                        <p className="mt-4 rounded-lg bg-slate-100 dark:bg-slate-800 px-3 py-2 text-sm text-slate-600 dark:text-slate-300">
                            {pageMessage}
                        </p>
                    )}
                </section>

                <section className="space-y-3">
                    <div className="flex items-center justify-between">
                        <h2 className="text-lg font-black">Ofertas disponibles para canjear</h2>
                        <button
                            onClick={() => navigate("/offers")}
                            className="text-sm font-bold text-primary hover:underline"
                        >
                            Ver catálogo completo
                        </button>
                    </div>

                    {offersQuery.isLoading ? (
                        <div className="py-10 flex justify-center"><Loader /></div>
                    ) : offers.length === 0 ? (
                        <div className="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 text-sm text-slate-500">
                            No hay ofertas canjeables por puntos en este momento.
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            {offers.map((offer) => {
                                const canRedeem = pointsBalance >= offer.pointsCost && offer.quantity > 0;
                                return (
                                    <article key={offer.id} className="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden">
                                        <div className="relative h-40">
                                            <img
                                                src={offer.image || getFallbackImage(offer.id, "offer")}
                                                alt={offer.title}
                                                className="w-full h-full object-cover"
                                            />
                                            <span className="absolute top-2 right-2 px-2 py-1 rounded-full text-[11px] font-black bg-primary text-white">
                                                {offer.pointsCost} VAC
                                            </span>
                                        </div>
                                        <div className="p-4 space-y-3">
                                            <h3 className="font-black line-clamp-2 min-h-[3rem]">{offer.title}</h3>
                                            <p className="text-xs text-slate-500 dark:text-slate-400 line-clamp-1">
                                                {offer.businessName ?? "Negocio"} · Stock {offer.quantity}
                                            </p>
                                            <div className="flex items-center gap-2">
                                                <button
                                                    onClick={() => redeemMutation.mutate(offer.id)}
                                                    disabled={!canRedeem || redeemMutation.isPending}
                                                    className="flex-1 px-3 py-2 rounded-lg bg-slate-900 text-white text-xs font-black disabled:opacity-40 disabled:cursor-not-allowed"
                                                >
                                                    {redeemMutation.isPending ? "Canjeando..." : "Canjear"}
                                                </button>
                                                <button
                                                    onClick={() => navigate(`/offer/${offer.slug}`)}
                                                    className="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-xs font-bold"
                                                >
                                                    Ver
                                                </button>
                                            </div>
                                        </div>
                                    </article>
                                );
                            })}
                        </div>
                    )}
                </section>

                <section className="space-y-3">
                    <h2 className="text-lg font-black">Mis QR canjeados</h2>

                    {redemptionsQuery.isLoading ? (
                        <div className="py-10 flex justify-center"><Loader /></div>
                    ) : (redemptionsQuery.data ?? []).length === 0 ? (
                        <div className="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 text-sm text-slate-500">
                            Aún no has canjeado ofertas. Cuando canjees una, aparecerá aquí su QR.
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            {(redemptionsQuery.data ?? []).map((item) => (
                                <article key={item.redemptionId} className="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 flex flex-col sm:flex-row gap-4">
                                    <img
                                        src={buildQrUrl(item.qrPayload)}
                                        alt={`QR ${item.redemptionId}`}
                                        className="w-[160px] h-[160px] rounded-lg border border-slate-200 dark:border-slate-700 bg-white"
                                    />
                                    <div className="flex-1 min-w-0">
                                        <h3 className="font-black text-base line-clamp-2">{item.offer.title}</h3>
                                        <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                            {item.offer.businessName ?? "Negocio"}
                                        </p>
                                        <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                            Canjeado: {item.redeemedAt ? new Date(item.redeemedAt).toLocaleString("es-ES") : "-"}
                                        </p>
                                        <p className="text-xs font-bold text-primary mt-2">{item.pointsSpent} VAC</p>
                                        <div className="mt-3 flex items-center gap-2">
                                            <button
                                                onClick={() => navigate(`/offer/${item.offer.slug}`)}
                                                className="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-xs font-bold"
                                            >
                                                Ver oferta
                                            </button>
                                            <button
                                                onClick={() => navigator.clipboard?.writeText(item.qrPayload)}
                                                className="px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-bold"
                                            >
                                                Copiar código
                                            </button>
                                        </div>
                                    </div>
                                </article>
                            ))}
                        </div>
                    )}
                </section>
            </main>

            <Footer />
        </div>
    );
}
