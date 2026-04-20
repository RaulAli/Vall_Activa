import { useMemo } from "react";
import { useNavigate } from "react-router-dom";
import { Header } from "../widgets/layout/Header";
import { useOffersListQuery } from "../features/offers/queries/useOffersListQuery";
import { useRoutesListQuery } from "../features/routes/queries/useRoutesListQuery";
import { getFallbackImage } from "../shared/utils/images";

const WORLD_BBOX = { minLng: -180, minLat: -85, maxLng: 180, maxLat: 85 };

const COUPONS = [
    { id: "c1", title: "Trail Gear Coupon", brand: "Alpine Experts", points: 500, value: "20%" },
    { id: "c2", title: "Kayak Rental", brand: "Aqua Tours", points: 300, value: "FREE" },
    { id: "c3", title: "Mountain Dinner", brand: "Sky Dine", points: 900, value: "50€" },
    { id: "c4", title: "Bike Day Pass", brand: "Alps Extreme", points: 350, value: "15%" },
];

type GuideCard = {
    slug: string;
    name: string;
    avatar: string | null;
    routeTitle: string;
    routeImage: string;
    routeSlug: string;
    hourlyRate: number;
    isVerified: boolean;
};

export function MarketplacePage() {
    const navigate = useNavigate();

    const { data: offersData, isLoading: loadingOffers } = useOffersListQuery({
        bbox: null,
        q: "",
        discountType: null,
        inStock: false,
        priceMin: null,
        priceMax: null,
        pointsMin: null,
        pointsMax: null,
        sort: "recent",
        order: "desc",
        page: 1,
        limit: 8,
        enabled: true,
    });

    const { data: routesPages, isLoading: loadingRoutes } = useRoutesListQuery({
        bbox: WORLD_BBOX,
        focusBbox: null,
        q: "",
        sportCode: null,
        guideOnly: true,
        distanceMin: null,
        distanceMax: null,
        gainMin: null,
        gainMax: null,
        difficulty: null,
        routeType: null,
        durationMin: null,
        durationMax: null,
        sort: "recent",
        order: "desc",
        enabled: true,
    });

    const hotDeals = useMemo(() => (offersData?.items ?? []).slice(0, 3), [offersData?.items]);

    const guides = useMemo<GuideCard[]>(() => {
        const routeItems = (routesPages?.pages ?? []).flatMap((p) => p.items);
        const uniqueByCreator = new Map<string, GuideCard>();

        for (const route of routeItems) {
            if (!route.creatorSlug || !route.creatorName) continue;
            if (route.guidePricePerHour === null || route.guidePricePerHour === undefined) continue;
            if (uniqueByCreator.has(route.creatorSlug)) continue;

            uniqueByCreator.set(route.creatorSlug, {
                slug: route.creatorSlug,
                name: route.creatorName,
                avatar: route.creatorAvatar ?? null,
                routeTitle: route.title,
                routeImage: route.image ?? getFallbackImage(route.id, "route"),
                routeSlug: route.slug,
                hourlyRate: route.guidePricePerHour,
                isVerified: route.creatorIsVerified === true,
            });
        }

        return Array.from(uniqueByCreator.values()).slice(0, 8);
    }, [routesPages?.pages]);

    return (
        <div className="min-h-screen bg-background-light dark:bg-background-dark">
            <Header />

            <main className="max-w-[1400px] mx-auto px-4 md:px-8 py-8 space-y-12">
                <section>
                    <div className="flex items-center gap-2 mb-4">
                        <span className="material-symbols-outlined text-secondary">local_fire_department</span>
                        <h1 className="text-2xl md:text-3xl font-black text-slate-900 dark:text-white">Hot Deals</h1>
                    </div>

                    {loadingOffers ? (
                        <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-8 flex justify-center">
                            <span className="w-6 h-6 border-2 border-primary/30 border-t-primary rounded-full animate-spin" />
                        </div>
                    ) : hotDeals.length === 0 ? (
                        <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-8 text-center text-slate-500">
                            Todavía no hay ofertas destacadas.
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-5">
                            {hotDeals.map((offer, index) => (
                                <article
                                    key={offer.id}
                                    className={`rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 ${index === 0 ? "lg:col-span-2" : ""}`}
                                >
                                    <div className="relative h-64 md:h-72">
                                        <img
                                            src={offer.image ?? getFallbackImage(offer.id, "offer")}
                                            alt={offer.title}
                                            className="w-full h-full object-cover"
                                        />
                                        <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent" />
                                        <div className="absolute bottom-0 left-0 w-full p-5 text-white">
                                            <p className="text-xs font-bold uppercase tracking-wider mb-1">{offer.businessName ?? "Guide Deal"}</p>
                                            <h2 className="text-xl md:text-2xl font-black leading-tight">{offer.title}</h2>
                                            <div className="flex items-center justify-between mt-3">
                                                <p className="text-sm font-semibold">{offer.price} {offer.currency}</p>
                                                <button
                                                    onClick={() => navigate(`/offer/${offer.slug}`)}
                                                    className="px-4 py-2 rounded-lg bg-primary text-white text-sm font-bold hover:bg-blue-600 transition-colors"
                                                >
                                                    Ver oferta
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            ))}
                        </div>
                    )}
                </section>

                <section>
                    <div className="flex items-center justify-between mb-4">
                        <div>
                            <h2 className="text-2xl font-black text-slate-900 dark:text-white">Rent a Guide</h2>
                            <p className="text-sm text-slate-500">Guías y rutas públicas listas para reservar.</p>
                        </div>
                        <button
                            onClick={() => navigate("/routes")}
                            className="text-sm font-bold text-primary hover:underline"
                        >
                            Ver rutas
                        </button>
                    </div>

                    {loadingRoutes ? (
                        <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-8 flex justify-center">
                            <span className="w-6 h-6 border-2 border-primary/30 border-t-primary rounded-full animate-spin" />
                        </div>
                    ) : guides.length === 0 ? (
                        <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-8 text-center text-slate-500">
                            Aún no hay guías con rutas públicas.
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                            {guides.map((guide) => (
                                <article key={guide.slug} className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                                    <div className="relative h-44">
                                        <img src={guide.routeImage} alt={guide.routeTitle} className="w-full h-full object-cover" />
                                        <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent" />
                                        <button
                                            onClick={() => navigate(`/profile/${guide.slug}`)}
                                            className="absolute bottom-3 left-3 flex items-center gap-2"
                                        >
                                            <div className="size-8 rounded-full bg-white overflow-hidden border border-white">
                                                {guide.avatar ? (
                                                    <img src={guide.avatar} alt={guide.name} className="w-full h-full object-cover" />
                                                ) : (
                                                    <div className="w-full h-full bg-primary/15 text-primary font-black text-xs flex items-center justify-center">
                                                        {guide.name.charAt(0).toUpperCase()}
                                                    </div>
                                                )}
                                            </div>
                                            <span className="text-white text-sm font-bold">{guide.name}</span>
                                        </button>
                                        {guide.isVerified && (
                                            <span className="absolute top-3 right-3 inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-500/90 text-white text-[11px] font-black uppercase tracking-wider">
                                                <span className="material-symbols-outlined !text-sm">verified</span>
                                                Verificado
                                            </span>
                                        )}
                                    </div>
                                    <div className="p-4">
                                        <h3 className="font-bold text-slate-900 dark:text-white line-clamp-2 min-h-[2.75rem]">{guide.routeTitle}</h3>
                                        <div className="mt-3 flex items-center justify-between">
                                            <p className="text-sm text-slate-500">
                                                <span className="font-black text-primary">€{guide.hourlyRate}</span>/hora
                                            </p>
                                            <button
                                                onClick={() => navigate(`/route/${guide.routeSlug}`)}
                                                className="px-3 py-1.5 rounded-lg bg-primary text-white text-xs font-bold hover:bg-blue-600"
                                            >
                                                Reservar
                                            </button>
                                        </div>
                                    </div>
                                </article>
                            ))}
                        </div>
                    )}
                </section>

                <section>
                    <div className="flex items-center justify-between mb-4">
                        <div>
                            <h2 className="text-2xl font-black text-slate-900 dark:text-white">Shop Coupons</h2>
                            <p className="text-sm text-slate-500">Canjea tus puntos por descuentos y beneficios.</p>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                        {COUPONS.map((coupon) => (
                            <article key={coupon.id} className="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                                <p className="text-xs font-black uppercase tracking-wider text-slate-400">{coupon.brand}</p>
                                <h3 className="mt-1 font-black text-slate-900 dark:text-white">{coupon.title}</h3>
                                <div className="mt-5 py-5 border-y border-dashed border-slate-200 dark:border-slate-700 text-center">
                                    <p className="text-3xl font-black text-primary">{coupon.value}</p>
                                </div>
                                <div className="mt-4 flex items-center justify-between">
                                    <p className="text-xs text-slate-500">Coste: {coupon.points} pts</p>
                                    <button className="text-xs font-bold px-3 py-1.5 rounded-lg border border-primary text-primary hover:bg-primary hover:text-white transition-colors">
                                        Canjear
                                    </button>
                                </div>
                            </article>
                        ))}
                    </div>
                </section>
            </main>
        </div>
    );
}
