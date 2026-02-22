import { useParams, useNavigate } from "react-router-dom";
import { useOfferDetailsQuery } from "../features/offers/queries/useOfferDetailsQuery";
import { Loader } from "../shared/ui/Loader";
import { ErrorState } from "../shared/ui/ErrorState";
import { Header } from "../widgets/layout/Header";
import { Footer } from "../widgets/layout/Footer";
import { getFallbackImage } from "../shared/utils/images";
import { DetailsMap } from "../shared/ui/DetailsMap";

export function OfferDetailPage() {
    const { slug } = useParams<{ slug: string }>();
    const navigate = useNavigate();
    const { data: offer, isLoading, error } = useOfferDetailsQuery(slug || null);

    if (isLoading) return <div className="h-screen flex items-center justify-center"><Loader /></div>;
    if (error || !offer) return <ErrorState message="No se pudo cargar la oferta" />;

    return (
        <div className="relative flex flex-col w-full overflow-x-hidden font-display bg-white dark:bg-background-dark text-slate-900 dark:text-white min-h-screen transition-colors duration-300">
            <Header />

            <main className="max-w-[1200px] mx-auto w-full px-4 md:px-10 min-h-screen">
                {/* Breadcrumbs */}
                <div className="flex flex-wrap items-center gap-2 py-6">
                    <a className="text-[#4c669a] text-sm font-medium hover:text-primary transition-colors" href="/">Home</a>
                    <span className="material-symbols-outlined text-sm text-[#4c669a]">chevron_right</span>
                    <a className="text-[#4c669a] text-sm font-medium hover:text-primary transition-colors" href="/">Offers</a>
                    <span className="material-symbols-outlined text-sm text-[#4c669a]">chevron_right</span>
                    <span className="text-[#0d121b] dark:text-white text-sm font-semibold">{offer.title}</span>
                </div>

                {/* Header Section */}
                <section className="mb-8">
                    <div className="relative w-full h-[400px] overflow-hidden rounded-xl bg-slate-200 dark:bg-slate-800 shadow-xl group">
                        <div
                            className="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-105"
                            style={{
                                backgroundImage: `linear-gradient(180deg, rgba(0, 0, 0, 0) 40%, rgba(0, 0, 0, 0.7) 100%), url("${offer.image || getFallbackImage(offer.id, "offer")}")`
                            }}
                        />
                        <div className="absolute bottom-0 left-0 p-8 w-full flex flex-col md:flex-row md:items-end justify-between gap-6">
                            <div>
                                <span className="inline-block px-3 py-1 rounded-full bg-primary text-white text-[10px] font-bold uppercase tracking-widest mb-3">
                                    {offer.discountType || "Oferta"}
                                </span>
                                <h1 className="text-white text-4xl md:text-5xl font-extrabold tracking-tight">{offer.title}</h1>
                                {(offer.businessName || offer.businessAvatar) && (
                                    <button
                                        onClick={() => offer.businessSlug && navigate(`/profile/${offer.businessSlug}`)}
                                        disabled={!offer.businessSlug}
                                        className="flex items-center gap-2 mt-4 text-white/90 hover:text-white transition-colors disabled:cursor-default group/biz"
                                    >
                                        <div className="size-6 rounded-full bg-white/20 overflow-hidden flex items-center justify-center group-hover/biz:ring-2 group-hover/biz:ring-white/50 transition-all">
                                            {offer.businessAvatar
                                                ? <img src={offer.businessAvatar} alt={offer.businessName ?? ""} className="w-full h-full object-cover" />
                                                : <span className="text-[9px] font-extrabold text-white">{offer.businessName?.charAt(0)?.toUpperCase()}</span>
                                            }
                                        </div>
                                        <span className="text-sm font-bold">{offer.businessName}</span>
                                        {offer.businessSlug && <span className="material-symbols-outlined !text-sm opacity-70">arrow_forward</span>}
                                    </button>
                                )}
                            </div>
                            <div className="flex flex-wrap gap-3">
                                <div className="bg-white/10 backdrop-blur-md rounded-xl p-4 border border-white/20 min-w-[120px] text-center">
                                    <p className="text-white/70 text-xs font-semibold uppercase">Precio</p>
                                    <p className="text-white text-2xl font-black">{offer.price} {offer.currency}</p>
                                </div>
                                <div className="bg-primary/20 backdrop-blur-md rounded-xl p-4 border border-primary/30 min-w-[120px] text-center border-2">
                                    <p className="text-white/70 text-xs font-semibold uppercase">O por solo</p>
                                    <p className="text-white text-2xl font-black">{offer.pointsCost} VAC</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Main Content Layout */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-10 pb-20">
                    {/* Left Column: Details */}
                    <div className="lg:col-span-2 space-y-10">
                        {/* Description */}
                        <section>
                            <h2 className="text-2xl font-bold mb-4 flex items-center gap-2">
                                <span className="material-symbols-outlined text-primary">description</span>
                                Detalles de la oferta
                            </h2>
                            <div className="prose dark:prose-invert max-w-none text-slate-600 dark:text-slate-400 leading-relaxed">
                                <p className="mb-4 text-lg">
                                    {offer.description || "Esta es una oferta exclusiva disponible a través de VAMO. Aprovecha esta oportunidad única para disfrutar de servicios premium a precios reducidos o canjeando tus puntos de actividad."}
                                </p>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-8">
                                    <div className="p-5 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                                        <div className="flex items-center gap-2 mb-2 text-primary">
                                            <span className="material-symbols-outlined">inventory_2</span>
                                            <h4 className="font-bold text-sm uppercase tracking-tight">Stock Disponible</h4>
                                        </div>
                                        <p className="text-2xl font-bold">{offer.quantity} unidades</p>
                                    </div>
                                    <div className="p-5 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                                        <div className="flex items-center gap-2 mb-2 text-primary">
                                            <span className="material-symbols-outlined">verified</span>
                                            <h4 className="font-bold text-sm uppercase tracking-tight">Estado</h4>
                                        </div>
                                        <p className="text-2xl font-bold capitalize">{offer.status}</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section className="bg-slate-50 dark:bg-slate-900 p-8 rounded-2xl border border-slate-200 dark:border-slate-800">
                            <h3 className="text-xl font-bold mb-4 flex items-center gap-2">
                                <span className="material-symbols-outlined text-primary">store</span>
                                Sobre el establecimiento
                            </h3>
                            <p className="text-slate-600 dark:text-slate-400 mb-6">
                                Este establecimiento colabora con VAMO para ofrecer las mejores experiencias y productos a nuestra comunidad de deportistas.
                            </p>

                            {offer.lat && offer.lng && (
                                <div className="w-full aspect-video rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 shadow-inner mb-6">
                                    <DetailsMap marker={{ lat: offer.lat, lng: offer.lng, title: offer.businessName ?? undefined }} />
                                </div>
                            )}

                            <button
                                onClick={() => offer.businessSlug && navigate(`/profile/${offer.businessSlug}`)}
                                disabled={!offer.businessSlug}
                                className="flex items-center gap-2 text-primary font-bold hover:underline transition-all disabled:opacity-50 disabled:cursor-default"
                            >
                                Ver perfil del negocio <span className="material-symbols-outlined !text-sm">arrow_forward</span>
                            </button>
                        </section>
                    </div>

                    {/* Right Column: Sticky Sidebar */}
                    <div className="lg:col-span-1">
                        <div className="sticky top-24 space-y-6">
                            {/* Action Card */}
                            <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden">
                                <div className="p-6">
                                    <h3 className="text-xl font-bold mb-6">Canjear Oferta</h3>

                                    <div className="space-y-4 mb-8">
                                        <div className="flex items-center justify-between py-2">
                                            <span className="text-sm font-medium text-slate-600 dark:text-slate-400">Total en puntos</span>
                                            <span className="text-xl font-black text-primary">{offer.pointsCost} VAC</span>
                                        </div>
                                        <div className="p-4 bg-primary/5 dark:bg-primary/10 rounded-lg text-[11px] text-slate-500 flex items-start gap-2 italic">
                                            <span className="material-symbols-outlined !text-sm mt-0.5">info</span>
                                            Al canjear esta oferta, recibirás un cupón digital para presentar en el establecimiento.
                                        </div>
                                    </div>

                                    <div className="space-y-3">
                                        <button className="w-full bg-primary hover:bg-blue-700 text-white font-bold py-4 rounded-xl flex items-center justify-center gap-2 transition-all shadow-lg shadow-primary/20">
                                            <span className="material-symbols-outlined">shopping_cart</span>
                                            Comprar ahora
                                        </button>
                                        <button className="w-full bg-slate-900 hover:bg-black text-white font-bold py-4 rounded-xl flex items-center justify-center gap-2 transition-all shadow-lg">
                                            <span className="material-symbols-outlined">stars</span>
                                            Canjear con {offer.pointsCost} VAC
                                        </button>
                                    </div>
                                </div>
                                <div className="p-6 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex items-center gap-4">
                                    <span className="material-symbols-outlined text-slate-400">share</span>
                                    <span className="text-xs font-bold text-slate-500">Compartir esta oferta</span>
                                </div>
                            </div>

                            {/* Terms Card */}
                            <div className="p-6 bg-slate-100 dark:bg-slate-800 rounded-2xl border border-transparent dark:border-slate-800">
                                <h4 className="font-bold text-sm mb-3 uppercase tracking-wider text-slate-500">Condiciones</h4>
                                <ul className="space-y-2 text-xs text-slate-600 dark:text-slate-400">
                                    <li className="flex gap-2">
                                        <span className="material-symbols-outlined !text-xs text-primary mt-0.5">check_circle</span>
                                        Sujeto a disponibilidad en el local.
                                    </li>
                                    <li className="flex gap-2">
                                        <span className="material-symbols-outlined !text-xs text-primary mt-0.5">check_circle</span>
                                        Válido hasta agotar existencias.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <Footer />
        </div>
    );
}
