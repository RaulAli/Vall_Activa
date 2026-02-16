import { useState } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import { useShopStore } from "../../store/shopStore";

export function Header() {
    const navigate = useNavigate();
    const location = useLocation();
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

    const isHome = location.pathname === "/";
    const isOffers = location.pathname === "/offers";
    const isRoutes = location.pathname === "/routes";

    const handleGoToDiscover = (newTab: "routes" | "offers") => {
        navigate(`/${newTab}`);
        setMobileMenuOpen(false);
    };

    const handleGoHome = () => {
        navigate("/");
        setMobileMenuOpen(false);
    };

    return (
        <header className="sticky top-0 z-[100] w-full bg-white/95 dark:bg-background-dark/95 backdrop-blur-sm border-b border-slate-200 dark:border-slate-800">
            <div className="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
                <div className="flex items-center justify-between h-16 sm:h-20">
                    {/* Logo */}
                    <div className="flex items-center gap-2 cursor-pointer group" onClick={handleGoHome}>
                        <div className="size-8 sm:size-10 bg-primary/10 rounded-xl flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-all duration-300 shadow-sm">
                            <span className="material-symbols-outlined !text-2xl sm:!text-3xl font-bold">explore</span>
                        </div>
                        <h1 className="text-lg sm:text-xl font-black tracking-tighter text-slate-900 dark:text-white">RouteFind</h1>
                    </div>

                    {/* Desktop Nav */}
                    <nav className="hidden md:flex items-center gap-1 bg-slate-100/50 dark:bg-slate-800/50 p-1 rounded-2xl border border-slate-200/50 dark:border-slate-700/50">
                        <button
                            onClick={handleGoHome}
                            className={`px-4 py-1.5 text-sm font-bold rounded-xl transition-all ${isHome ? "bg-white dark:bg-slate-900 text-primary shadow-sm ring-1 ring-black/5" : "text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white"}`}
                        >
                            Home
                        </button>
                        <button className="px-4 py-1.5 text-sm font-medium text-slate-400 dark:text-slate-500 cursor-not-allowed opacity-50" title="Próximamente">Marketplace</button>
                        <button
                            onClick={() => handleGoToDiscover("offers")}
                            className={`px-4 py-1.5 text-sm font-bold rounded-xl transition-all ${isOffers ? "bg-white dark:bg-slate-900 text-primary shadow-sm ring-1 ring-black/5" : "text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white"}`}
                        >
                            Offers
                        </button>
                        <button
                            onClick={() => handleGoToDiscover("routes")}
                            className={`px-4 py-1.5 text-sm font-bold rounded-xl transition-all ${isRoutes ? "bg-white dark:bg-slate-900 text-primary shadow-sm ring-1 ring-black/5" : "text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white"}`}
                        >
                            Routes
                        </button>
                    </nav>

                    {/* Actions */}
                    <div className="hidden md:flex items-center gap-3">
                        <button className="p-2 text-slate-400 dark:text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                            <span className="material-symbols-outlined">notifications</span>
                        </button>
                        <div className="h-4 w-px bg-slate-200 dark:bg-slate-800 mx-1" />
                        <button className="px-4 py-2 text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all">
                            Login
                        </button>
                        <button className="px-5 py-2.5 text-sm font-black text-white bg-primary hover:bg-blue-600 rounded-xl shadow-lg shadow-primary/20 transition-all active:scale-95">
                            Sign Up
                        </button>
                    </div>

                    {/* Mobile Menu Button */}
                    <button
                        onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                        className="md:hidden p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                    >
                        <span className="material-symbols-outlined">{mobileMenuOpen ? 'close' : 'menu'}</span>
                    </button>
                </div>
            </div>

            {/* Mobile Menu */}
            {mobileMenuOpen && (
                <div className="md:hidden bg-white dark:bg-background-dark border-b border-slate-200 dark:border-slate-800 px-4 py-6 flex flex-col gap-4 animate-in fade-in slide-in-from-top-1">
                    <button className={`text-left font-bold ${isHome ? "text-primary" : "text-slate-500"}`} onClick={handleGoHome}>Home</button>
                    <button className="text-left font-medium text-slate-400 opacity-50 cursor-not-allowed">Marketplace</button>
                    <button className={`text-left font-bold ${isOffers ? "text-primary" : "text-slate-500"}`} onClick={() => handleGoToDiscover("offers")}>Offers</button>
                    <button className={`text-left font-bold ${isRoutes ? "text-primary" : "text-slate-500"}`} onClick={() => handleGoToDiscover("routes")}>Routes</button>
                    <div className="h-px bg-slate-100 dark:bg-slate-800 my-2" />
                    <button className="w-full py-3 text-center font-bold text-white bg-primary rounded-xl shadow-lg">Sign Up</button>
                </div>
            )}
        </header>
    );
}
