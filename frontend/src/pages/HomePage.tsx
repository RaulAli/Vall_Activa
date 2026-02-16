import { useNavigate } from "react-router-dom";
import { useMemo } from "react";
import { Header } from "../widgets/layout/Header";
import { useRoutesListQuery } from "../features/routes/queries/useRoutesListQuery";
import { useOffersListQuery } from "../features/offers/queries/useOffersListQuery";
import { getFallbackImage } from "../shared/utils/images";

export function HomePage() {
    const navigate = useNavigate();

    const routesQuery = useRoutesListQuery({
        // ... routes params
        bbox: { minLng: -10, minLat: 35, maxLng: 5, maxLat: 45 },
        focusBbox: null,
        q: "",
        sportCode: null,
        distanceMin: null,
        distanceMax: null,
        gainMin: null,
        gainMax: null,
        sort: "recent",
        order: "desc",
        page: 1,
        limit: 12,
        enabled: true
    });

    const offersQuery = useOffersListQuery({
        bbox: null,
        q: "",
        discountType: null,
        inStock: true,
        priceMin: null,
        priceMax: null,
        pointsMin: null,
        pointsMax: null,
        sort: "recent",
        order: "desc",
        page: 1,
        limit: 12,
        enabled: true
    });

    const displayRoutes = useMemo(() => {
        if (!routesQuery.data?.items || routesQuery.data.items.length === 0) return [];
        return [...routesQuery.data.items]
            .sort(() => 0.5 - Math.random())
            .slice(0, 4);
    }, [routesQuery.data?.items]);

    const displayOffers = useMemo(() => {
        if (!offersQuery.data?.items || offersQuery.data.items.length === 0) return [];
        return [...offersQuery.data.items]
            .sort(() => 0.5 - Math.random())
            .slice(0, 4);
    }, [offersQuery.data?.items]);

    const handleGoToDiscover = (tab: "routes" | "offers") => {
        navigate(`/${tab}`);
    };

    const handleGoToRoute = (slug: string) => {
        navigate(`/route/${slug}`);
    };

    const handleGoToOffer = (slug: string) => {
        navigate(`/offer/${slug}`);
    };

    return (
        <div className="bg-white dark:bg-background-dark text-slate-900 dark:text-white font-display antialiased flex flex-col min-h-screen overflow-x-hidden">
            <Header />

            <main className="flex-grow">
                {/* Hero Section */}
                <section className="relative w-full min-h-[600px] lg:min-h-[700px] flex items-center justify-center bg-cover bg-center" style={{ backgroundImage: `linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&q=80&w=2070')` }}>
                    <div className="absolute inset-0 bg-gradient-to-b from-transparent via-black/20 to-white dark:to-background-dark"></div>
                    <div className="relative z-10 w-full max-w-5xl px-4 flex flex-col items-center gap-10 text-center">
                        <div className="space-y-6">
                            <h1 className="text-5xl md:text-7xl lg:text-8xl font-black text-white leading-[1.1] tracking-tighter drop-shadow-2xl">
                                Find your path.<br />Guide your adventure.
                            </h1>
                            <p className="text-lg md:text-2xl text-white/90 font-medium max-w-2xl mx-auto drop-shadow-md leading-relaxed">
                                Curated routes by local experts. Discover the world's hidden trails and premium gear for every journey.
                            </p>
                        </div>
                        {/* Search Bar Component */}
                        <div className="w-full max-w-2xl p-2 bg-white/10 dark:bg-slate-900/40 backdrop-blur-xl rounded-[2rem] shadow-2xl transform transition-transform hover:scale-[1.01] border border-white/20">
                            <div className="bg-white dark:bg-slate-900 rounded-[1.7rem] flex items-center w-full h-16 md:h-20 shadow-inner">
                                <div className="pl-6 pr-3 text-slate-400 flex items-center justify-center">
                                    <span className="material-symbols-outlined !text-3xl">search</span>
                                </div>
                                <input
                                    className="flex-1 h-full bg-transparent border-none focus:ring-0 text-slate-900 dark:text-white placeholder-slate-400 text-xl font-medium"
                                    placeholder="Where do you want to go?"
                                    type="text"
                                />
                                <button
                                    onClick={() => handleGoToDiscover("routes")}
                                    className="h-12 md:h-16 px-10 bg-primary hover:bg-blue-600 text-white font-extrabold rounded-2xl transition-all shadow-xl active:scale-95 mr-2"
                                >
                                    Search
                                </button>
                            </div>
                        </div>
                        {/* Quick tags */}
                        <div className="flex flex-wrap justify-center gap-3">
                            {["Hiking", "Cycling", "Road Trip", "Mountaineering"].map(tag => (
                                <span key={tag} className="px-5 py-2 bg-white/10 hover:bg-white/20 backdrop-blur-md rounded-full text-white text-[11px] font-black uppercase tracking-widest cursor-pointer border border-white/20 transition-all hover:scale-105 active:scale-95">
                                    {tag}
                                </span>
                            ))}
                        </div>
                    </div>
                </section>

                {/* Features Section */}
                <section className="py-24 px-4 bg-white dark:bg-background-dark">
                    <div className="max-w-[1280px] mx-auto">
                        <div className="text-center max-w-2xl mx-auto mb-20 animate-in">
                            <h2 className="text-4xl md:text-6xl font-black mb-6 tracking-tight">How It Works</h2>
                            <p className="text-slate-500 dark:text-slate-400 text-xl font-medium leading-relaxed">Start your next journey in three simple steps.</p>
                        </div>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-12">
                            {[
                                { icon: "map", title: "Discover", desc: "Search for a destination anywhere in the world and browse curated paths." },
                                { icon: "calendar_month", title: "Book", desc: "Choose a verified local guide or download a self-guided route pack." },
                                { icon: "hiking", title: "Explore", desc: "Pack your bags, sync your maps offline, and start your next adventure." }
                            ].map((step, i) => (
                                <div key={i} className="group flex flex-col bg-slate-50/50 dark:bg-slate-900/50 p-12 rounded-[3rem] border border-slate-100 dark:border-slate-800 hover:border-primary/50 transition-all duration-500 hover:shadow-2xl hover:-translate-y-2">
                                    <div className="w-20 h-20 bg-primary/10 rounded-3xl flex items-center justify-center text-primary mb-10 group-hover:bg-primary group-hover:text-white transition-all duration-500 group-hover:rotate-6">
                                        <span className="material-symbols-outlined !text-5xl">{step.icon}</span>
                                    </div>
                                    <h3 className="text-3xl font-black mb-4 tracking-tight">{step.title}</h3>
                                    <p className="text-slate-500 dark:text-slate-400 leading-relaxed font-semibold text-lg">
                                        {step.desc}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* Trending Routes Section */}
                <section className="py-24 px-4 bg-slate-50 dark:bg-[#080c14]">
                    <div className="max-w-[1280px] mx-auto">
                        <div className="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6">
                            <div className="space-y-4">
                                <h2 className="text-4xl md:text-5xl font-black tracking-tight text-slate-900 dark:text-white">Trending Routes</h2>
                                <p className="text-slate-500 dark:text-slate-400 font-bold text-lg">Featured destinations recommended by our community.</p>
                            </div>
                            <button
                                onClick={() => handleGoToDiscover("routes")}
                                className="w-fit px-8 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-primary hover:text-white hover:bg-primary transition-all font-black rounded-2xl shadow-sm flex items-center gap-2 group"
                            >
                                View all <span className="material-symbols-outlined !text-xl group-hover:translate-x-1 transition-transform">arrow_forward</span>
                            </button>
                        </div>
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
                            {routesQuery.isLoading && (
                                <>
                                    {[...Array(4)].map((_, i) => (
                                        <div key={i} className="animate-pulse flex flex-col gap-6">
                                            <div className="w-full aspect-[4/5] bg-slate-200 dark:bg-slate-800 rounded-[2.5rem]" />
                                            <div className="h-6 bg-slate-200 dark:bg-slate-800 rounded-lg w-3/4 mx-2" />
                                        </div>
                                    ))}
                                </>
                            )}

                            {!routesQuery.isLoading && displayRoutes.map((route, i) => {
                                const distanceKm = Math.round(route.distanceM / 1000);
                                const img = route.image || getFallbackImage(route.id, "route");

                                return (
                                    <div
                                        key={route.id}
                                        className="group flex flex-col gap-6 cursor-pointer"
                                        onClick={() => handleGoToRoute(route.slug)}
                                    >
                                        <div className="relative w-full aspect-[4/5] rounded-[2.5rem] overflow-hidden shadow-2xl bg-slate-200 dark:bg-slate-800 ring-1 ring-black/5 dark:ring-white/5">
                                            <div className="absolute top-5 right-5 z-10 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm px-4 py-2 rounded-2xl text-[11px] font-black shadow-lg flex items-center gap-1.5 text-slate-900 dark:text-white ring-1 ring-black/5">
                                                <span className="material-symbols-outlined !text-sm text-yellow-500 font-variation-fill">star</span> {4.5 + (i % 5) / 10}
                                            </div>
                                            <img
                                                src={route.image ? `${img}?auto=format&fit=crop&q=80&w=800` : img}
                                                alt={route.title}
                                                className="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110"
                                            />
                                            <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-60 group-hover:opacity-40 transition-opacity duration-500" />
                                            <div className="absolute bottom-6 left-6 right-6 flex justify-between items-end text-white">
                                                <div className="space-y-1">
                                                    <span className={`px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-wider bg-white/20 backdrop-blur-md border border-white/20`}>
                                                        {route.elevationGainM > 1000 ? "Difficult" : route.elevationGainM > 500 ? "Medium" : "Easy"}
                                                    </span>
                                                    <p className="font-bold text-sm tracking-tight">{distanceKm} km · +{route.elevationGainM}m</p>
                                                </div>
                                                <span className="text-sm font-black uppercase tracking-tighter">View Path</span>
                                            </div>
                                        </div>
                                        <div className="px-2">
                                            <h3 className="text-xl font-black leading-tight group-hover:text-primary transition-colors text-slate-900 dark:text-white tracking-tight">{route.title}</h3>
                                        </div>
                                    </div>
                                );
                            })}

                            {displayRoutes.length === 0 && !routesQuery.isLoading && (
                                <div className="col-span-full py-20 text-center opacity-50">
                                    <p className="text-xl font-bold">No tracks found yet</p>
                                </div>
                            )}
                        </div>
                    </div>
                </section>

                {/* Exclusive Offers Section */}
                <section className="py-24 px-4 bg-white dark:bg-background-dark">
                    <div className="max-w-[1280px] mx-auto">
                        <div className="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6">
                            <div className="space-y-4">
                                <h2 className="text-4xl md:text-5xl font-black tracking-tight text-slate-900 dark:text-white">Exclusive Offers</h2>
                                <p className="text-slate-500 dark:text-slate-400 font-bold text-lg">Premium gear and experiences at member prices.</p>
                            </div>
                            <button
                                onClick={() => handleGoToDiscover("offers")}
                                className="w-fit px-8 py-3 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-primary hover:text-white hover:bg-primary transition-all font-black rounded-2xl shadow-sm flex items-center gap-2 group"
                            >
                                View all <span className="material-symbols-outlined !text-xl group-hover:translate-x-1 transition-transform">arrow_forward</span>
                            </button>
                        </div>
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
                            {offersQuery.isLoading && (
                                <>
                                    {[...Array(4)].map((_, i) => (
                                        <div key={i} className="animate-pulse flex flex-col gap-6">
                                            <div className="w-full aspect-square bg-slate-200 dark:bg-slate-800 rounded-[2.5rem]" />
                                            <div className="h-6 bg-slate-200 dark:bg-slate-800 rounded-lg w-3/4 mx-2" />
                                        </div>
                                    ))}
                                </>
                            )}

                            {!offersQuery.isLoading && displayOffers.map((offer, i) => {
                                const img = offer.image || getFallbackImage(offer.id, "offer");

                                return (
                                    <div
                                        key={offer.id}
                                        className="group flex flex-col gap-6 cursor-pointer"
                                        onClick={() => handleGoToOffer(offer.slug)}
                                    >
                                        <div className="relative w-full aspect-square rounded-[2.5rem] overflow-hidden shadow-2xl bg-slate-200 dark:bg-slate-800 ring-1 ring-black/5 dark:ring-white/5">
                                            <div className="absolute top-5 left-5 z-10 bg-primary px-4 py-2 rounded-2xl text-[11px] font-black shadow-lg text-white ring-1 ring-white/20">
                                                -{offer.pointsCost} VAC
                                            </div>
                                            <img
                                                src={offer.image ? `${img}?auto=format&fit=crop&q=80&w=800` : img}
                                                alt={offer.title}
                                                className="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110"
                                            />
                                            <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-60 group-hover:opacity-40 transition-opacity duration-500" />
                                            <div className="absolute bottom-6 left-6 right-6 flex justify-between items-end text-white">
                                                <div className="space-y-1">
                                                    <span className="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-wider bg-white/20 backdrop-blur-md border border-white/20">
                                                        {offer.discountType}
                                                    </span>
                                                    <p className="font-bold text-sm tracking-tight">{offer.price} {offer.currency}</p>
                                                </div>
                                                <span className="text-sm font-black uppercase tracking-tighter">Get Reward</span>
                                            </div>
                                        </div>
                                        <div className="px-2">
                                            <h3 className="text-xl font-black leading-tight group-hover:text-primary transition-colors text-slate-900 dark:text-white tracking-tight line-clamp-2">{offer.title}</h3>
                                        </div>
                                    </div>
                                );
                            })}

                            {displayOffers.length === 0 && !offersQuery.isLoading && (
                                <div className="col-span-full py-20 text-center opacity-50">
                                    <p className="text-xl font-bold">No offers available yet</p>
                                </div>
                            )}
                        </div>
                    </div>
                </section>

                {/* CTA Banner */}
                <section className="py-32 px-4 overflow-hidden bg-white dark:bg-background-dark">
                    <div className="max-w-[1280px] mx-auto bg-primary rounded-[4rem] overflow-hidden relative shadow-3xl shadow-primary/40">
                        <div className="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/topography.png')] mix-blend-overlay"></div>
                        <div className="absolute right-0 top-0 h-full w-1/2 bg-gradient-to-l from-black/20 to-transparent hidden lg:block"></div>
                        <div className="relative z-10 flex flex-col lg:flex-row items-center justify-between p-16 md:p-24 lg:p-32 gap-12">
                            <div className="flex flex-col gap-8 max-w-2xl text-center lg:text-left">
                                <h2 className="text-5xl md:text-7xl font-black text-white leading-[1] tracking-tighter">Become a Local Guide</h2>
                                <p className="text-blue-100 text-xl md:text-2xl font-semibold leading-relaxed max-w-xl">Share your favorite hidden paths and earn rewards doing what you love.</p>
                            </div>
                            <div className="flex flex-col sm:flex-row gap-6 w-full sm:w-auto">
                                <button className="px-12 py-5 bg-white text-primary font-black rounded-3xl hover:bg-blue-50 transition-all shadow-2xl hover:-translate-y-1.5 active:scale-95 text-xl tracking-tight">
                                    Get Started
                                </button>
                                <button className="px-10 py-5 bg-primary border-2 border-white/30 text-white font-black rounded-3xl hover:bg-white/10 transition-all text-xl backdrop-blur-sm tracking-tight">
                                    Learn More
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            {/* Footer */}
            <footer className="border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-background-dark pt-24 pb-12 transition-colors">
                <div className="max-w-[1280px] mx-auto px-8">
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-16 mb-20">
                        <div className="flex flex-col gap-8">
                            <div className="flex items-center gap-2">
                                <span className="material-symbols-outlined text-primary text-4xl font-black">explore</span>
                                <h2 className="text-3xl font-black tracking-tighter">RouteFind</h2>
                            </div>
                            <p className="text-slate-500 dark:text-slate-400 font-bold text-lg leading-relaxed">
                                Curating the best adventures since 2024. Exploration starts here.
                            </p>
                        </div>
                        {[
                            { title: "Platform", links: ["Marketplace", "Browse Routes", "Become a Guide"] },
                            { title: "Company", links: ["About Us", "Careers", "Blog"] },
                            { title: "Support", links: ["Help Center", "Terms", "Privacy"] }
                        ].map((col, i) => (
                            <div key={i}>
                                <h3 className="font-black text-xl mb-8 tracking-tight">{col.title}</h3>
                                <ul className="space-y-4">
                                    {col.links.map(link => (
                                        <li key={link}><a className="text-slate-500 dark:text-slate-400 hover:text-primary transition-colors font-bold text-lg" href="#">{link}</a></li>
                                    ))}
                                </ul>
                            </div>
                        ))}
                    </div>
                    <div className="pt-10 border-t border-slate-200 dark:border-slate-800 flex flex-col md:flex-row justify-between items-center gap-8">
                        <p className="text-slate-400 font-black text-[12px] uppercase tracking-[0.2em]">© 2024 RouteFind Inc. All rights reserved.</p>
                        <div className="flex gap-8 text-slate-400">
                            <a href="#" className="hover:text-primary transition-all hover:scale-110"><span className="material-symbols-outlined !text-3xl">public</span></a>
                            <a href="#" className="hover:text-primary transition-all hover:scale-110"><span className="material-symbols-outlined !text-3xl">mail</span></a>
                            <a href="#" className="hover:text-primary transition-all hover:scale-110"><span className="material-symbols-outlined !text-3xl">share</span></a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    );
}
