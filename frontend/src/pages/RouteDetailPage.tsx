import { useParams } from "react-router-dom";
import { useRouteDetailsQuery } from "../features/routes/queries/useRouteDetailsQuery";
import { Loader } from "../shared/ui/Loader";
import { ErrorState } from "../shared/ui/ErrorState";
import { Header } from "../widgets/layout/Header";
import { Footer } from "../widgets/layout/Footer";

export function RouteDetailPage() {
    const { slug } = useParams<{ slug: string }>();
    const { data: route, isLoading, error } = useRouteDetailsQuery(slug || null);

    if (isLoading) return <div className="h-screen flex items-center justify-center"><Loader /></div>;
    if (error || !route) return <ErrorState message="No se pudo cargar la ruta" />;

    return (
        <div className="relative flex flex-col w-full overflow-x-hidden font-display">
            <Header />

            <main className="max-w-[1200px] mx-auto w-full px-4 md:px-10">
                {/* Breadcrumbs */}
                <div className="flex flex-wrap items-center gap-2 py-6">
                    <a className="text-[#4c669a] text-sm font-medium hover:text-primary transition-colors" href="/">Home</a>
                    <span className="material-symbols-outlined text-sm text-[#4c669a]">chevron_right</span>
                    <a className="text-[#4c669a] text-sm font-medium hover:text-primary transition-colors" href="/">Routes</a>
                    <span className="material-symbols-outlined text-sm text-[#4c669a]">chevron_right</span>
                    <span className="text-[#0d121b] dark:text-white text-sm font-semibold">{route.title}</span>
                </div>

                {/* Header Section */}
                <section className="mb-8">
                    <div className="relative w-full h-[450px] overflow-hidden rounded-xl bg-slate-200 dark:bg-slate-800 shadow-xl group">
                        <div
                            className="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-105"
                            style={{
                                backgroundImage: `linear-gradient(180deg, rgba(0, 0, 0, 0) 40%, rgba(0, 0, 0, 0.7) 100%), url("https://lh3.googleusercontent.com/aida-public/AB6AXuBXuor0nDIRe85bV6sUcmzGVIGLzLzMAHvu0NJA6NBa4ahXPmiCQ0zwp4OnNKtBNYoxFdxqmjcAZeJu41VeWGvygmMulGfypiFpxJDJNGbvYypPxJw4T0fTrcKeHTnmUBrRxGY-DH4BB9eNrXIKld0XkpiXe2TGgwVVRPZzBF3IZOQtgp-duInP0qONMMbU1Bv8uAtzGo_VAIWjTM_Ncy2k47953PEQPkLD659d4i4QJki1V-kmuS0wF5dc7Sj96U2PC8HUzmWp-3UM")`
                            }}
                        />
                        <div className="absolute bottom-0 left-0 p-8 w-full flex flex-col md:flex-row md:items-end justify-between gap-6">
                            <div>
                                <span className="inline-block px-3 py-1 rounded-full bg-primary text-white text-xs font-bold uppercase tracking-wider mb-3">
                                    {route.sportId.toUpperCase()}
                                </span>
                                <h1 className="text-white text-4xl md:text-5xl font-extrabold tracking-tight">{route.title}</h1>
                                <div className="flex items-center gap-4 mt-4 text-white/90">
                                    <div className="flex items-center gap-1">
                                        <span className="material-symbols-outlined text-yellow-400">star</span>
                                        <span className="font-bold">4.9</span>
                                        <span className="text-sm opacity-75">(128 reviews)</span>
                                    </div>
                                    <span className="opacity-50">•</span>
                                    <div className="flex items-center gap-1">
                                        <span className="material-symbols-outlined text-sm">location_on</span>
                                        <span className="text-sm">Location Info</span>
                                    </div>
                                </div>
                            </div>
                            <div className="flex flex-wrap gap-3">
                                <div className="bg-white/10 backdrop-blur-md rounded-xl p-4 border border-white/20 min-w-[120px]">
                                    <p className="text-white/70 text-xs font-semibold uppercase">Distance</p>
                                    <p className="text-white text-xl font-bold">{(route.distanceM / 1000).toFixed(1)} km</p>
                                </div>
                                <div className="bg-white/10 backdrop-blur-md rounded-xl p-4 border border-white/20 min-w-[120px]">
                                    <p className="text-white/70 text-xs font-semibold uppercase">Elevation</p>
                                    <p className="text-white text-xl font-bold">{route.elevationGainM} m</p>
                                </div>
                                <div className="bg-white/10 backdrop-blur-md rounded-xl p-4 border border-white/20 min-w-[120px]">
                                    <p className="text-white/70 text-xs font-semibold uppercase">Created At</p>
                                    <p className="text-white text-lg font-bold">{new Date(route.createdAt).toLocaleDateString()}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Main Content Layout */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-10 pb-20">
                    {/* Left Column: Details & Map */}
                    <div className="lg:col-span-2 space-y-10">
                        {/* Description */}
                        <section>
                            <h2 className="text-2xl font-bold mb-4 flex items-center gap-2">
                                <span className="material-symbols-outlined text-primary">description</span>
                                About this route
                            </h2>
                            <div className="prose dark:prose-invert max-w-none text-slate-600 dark:text-slate-400 leading-relaxed">
                                <p className="mb-4">
                                    {route.description || "Esta ruta ofrece una experiencia increíble. Explora paisajes naturales únicos y disfruta del aire libre en una aventura diseñada para entusiastas de la montaña."}
                                </p>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                                    <div className="p-4 bg-background-light dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 transition-colors">
                                        <h4 className="font-bold text-sm mb-1 uppercase tracking-tight">Flora & Fauna</h4>
                                        <p className="text-sm opacity-80">Keep an eye out for marmots and the rare Edelweiss flowers during the summer months.</p>
                                    </div>
                                    <div className="p-4 bg-background-light dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 transition-colors">
                                        <h4 className="font-bold text-sm mb-1 uppercase tracking-tight">Best Season</h4>
                                        <p className="text-sm opacity-80">Ideally hiked between late June and September when the passes are clear of snow.</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        {/* Map Area */}
                        <section>
                            <div className="flex items-center justify-between mb-4">
                                <h2 className="text-2xl font-bold flex items-center gap-2">
                                    <span className="material-symbols-outlined text-primary">map</span>
                                    Interactive Map
                                </h2>
                                <button className="text-primary text-sm font-bold flex items-center gap-1 hover:underline transition-all">
                                    <span className="material-symbols-outlined text-sm">fullscreen</span>
                                    Full screen
                                </button>
                            </div>
                            <div className="relative w-full aspect-video rounded-xl bg-slate-200 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden group shadow-inner">
                                {/* Map will be integrated here */}
                                <div className="absolute inset-0 flex items-center justify-center bg-slate-100 dark:bg-slate-900">
                                    <span className="text-slate-400 font-medium">Map coming soon...</span>
                                </div>
                            </div>
                        </section>
                    </div>

                    {/* Right Column: Sticky Sidebar */}
                    <div className="lg:col-span-1">
                        <div className="sticky top-24 space-y-6">
                            {/* Route Info Card */}
                            <div className="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden transition-all">
                                <div className="p-6">
                                    <h3 className="text-xl font-bold mb-6">Route Information</h3>
                                    <div className="space-y-4 mb-8">
                                        <div className="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                                            <div className="flex items-center gap-3">
                                                <span className="material-symbols-outlined text-primary">signal_cellular_alt</span>
                                                <span className="text-sm font-medium text-slate-600 dark:text-slate-400">Difficulty</span>
                                            </div>
                                            <span className="px-2 py-1 rounded bg-orange-100 text-orange-700 text-[10px] font-bold uppercase">Advanced</span>
                                        </div>
                                        <div className="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                                            <div className="flex items-center gap-3">
                                                <span className="material-symbols-outlined text-primary">hiking</span>
                                                <span className="text-sm font-medium text-slate-600 dark:text-slate-400">Route Type</span>
                                            </div>
                                            <span className="text-sm font-bold text-slate-900 dark:text-white">One-way</span>
                                        </div>
                                        <div className="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                                            <div className="flex items-center gap-3">
                                                <span className="material-symbols-outlined text-primary">height</span>
                                                <span className="text-sm font-medium text-slate-600 dark:text-slate-400">Max Elevation</span>
                                            </div>
                                            <span className="text-sm font-bold text-slate-900 dark:text-white">2,450 m</span>
                                        </div>
                                    </div>
                                    <div className="space-y-3">
                                        <button className="w-full bg-primary hover:bg-blue-700 text-white font-bold py-4 rounded-xl flex items-center justify-center gap-2 transition-all shadow-lg shadow-primary/20">
                                            <span className="material-symbols-outlined">download</span>
                                            Download GPX
                                        </button>
                                        <button className="w-full bg-primary/10 hover:bg-primary/20 text-primary font-bold py-4 rounded-xl flex items-center justify-center gap-2 transition-all">
                                            <span className="material-symbols-outlined">print</span>
                                            Print Route Guide
                                        </button>
                                    </div>
                                </div>
                                <div className="bg-primary/5 dark:bg-primary/10 p-6 border-t border-primary/10">
                                    <div className="flex items-center gap-4">
                                        <div className="size-12 rounded-full bg-slate-300 border-2 border-white dark:border-slate-800" />
                                        <div>
                                            <p className="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">Verified Guide</p>
                                            <p className="text-sm font-bold text-slate-900 dark:text-white">Marco Steiger</p>
                                        </div>
                                        <button className="ml-auto text-primary hover:text-blue-700 transition-colors">
                                            <span className="material-symbols-outlined">chat</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {/* Local Weather Card */}
                            <div className="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-xl">
                                <div className="flex items-center justify-between mb-4">
                                    <h4 className="font-bold">Current Weather</h4>
                                    <span className="material-symbols-outlined">partly_cloudy_day</span>
                                </div>
                                <div className="flex items-end gap-2">
                                    <span className="text-3xl font-extrabold font-display">14°C</span>
                                    <span className="text-xs opacity-90 mb-1">Partly Cloudy</span>
                                </div>
                                <p className="text-xs mt-4 opacity-80 leading-relaxed italic">
                                    Ideal conditions for a traverse. Winds are low at 12km/h.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <Footer />
        </div>
    );
}
