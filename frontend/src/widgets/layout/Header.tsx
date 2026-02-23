import { useState, useRef, useEffect } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import { useShopStore } from "../../store/shopStore";
import { useAuthStore } from "../../store/authStore";
import { logout } from "../../features/auth/api/authApi";
import { getTheme, toggleTheme, type Theme } from "../../shared/utils/theme";

export function Header() {
    const navigate = useNavigate();
    const location = useLocation();
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const [themeState, setThemeState] = useState<Theme>(getTheme());
    const [userMenuOpen, setUserMenuOpen] = useState(false);
    const userMenuRef = useRef<HTMLDivElement>(null);

    const { isAuthenticated, user, token, clearAuth } = useAuthStore();

    // Close user menu on outside click
    useEffect(() => {
        function handleClick(e: MouseEvent) {
            if (userMenuRef.current && !userMenuRef.current.contains(e.target as Node)) {
                setUserMenuOpen(false);
            }
        }
        document.addEventListener("mousedown", handleClick);
        return () => document.removeEventListener("mousedown", handleClick);
    }, []);

    const handleLogout = async () => {
        setUserMenuOpen(false);
        try {
            if (token) await logout(token);
        } catch { /* ignore */ } finally {
            clearAuth();
            navigate("/");
        }
    };

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
        <header className="sticky top-0 z-[1100] w-full bg-white/95 dark:bg-background-dark/95 backdrop-blur-sm border-b border-slate-200 dark:border-slate-800">
            <div className="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
                <div className="flex items-center justify-between h-16 sm:h-20">
                    {/* Logo */}
                    <div className="flex items-center gap-2 cursor-pointer group" onClick={handleGoHome}>
                        <div className="size-8 sm:size-10 bg-primary/10 rounded-xl flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-all duration-300 shadow-sm">
                            <span className="material-symbols-outlined !text-2xl sm:!text-3xl font-bold">explore</span>
                        </div>
                        <h1 className="text-lg sm:text-xl font-black tracking-tighter text-slate-900 dark:text-white">Vall Activa</h1>
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
                        <button
                            onClick={() => setThemeState(toggleTheme())}
                            className="p-2 text-slate-400 dark:text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors"
                        >
                            <span className="material-symbols-outlined">
                                {themeState === "dark" ? "light_mode" : "dark_mode"}
                            </span>
                        </button>
                        <div className="h-4 w-px bg-slate-200 dark:bg-slate-800 mx-1" />

                        {isAuthenticated && user ? (
                            <div className="relative" ref={userMenuRef}>
                                <button
                                    onClick={() => setUserMenuOpen((v) => !v)}
                                    className="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-all"
                                >
                                    <div className="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-black text-sm overflow-hidden">
                                        {user.avatar ? (
                                            <img src={user.avatar} alt={user.name ?? ""} className="w-full h-full object-cover" />
                                        ) : (
                                            <span>{(user.name ?? user.email).charAt(0).toUpperCase()}</span>
                                        )}
                                    </div>
                                    <span className="text-sm font-bold text-slate-700 dark:text-slate-200 max-w-[120px] truncate">
                                        {user.name ?? user.email}
                                    </span>
                                    <span className="material-symbols-outlined !text-sm text-slate-400">
                                        {userMenuOpen ? "expand_less" : "expand_more"}
                                    </span>
                                </button>

                                {userMenuOpen && (
                                    <div className="absolute right-0 top-full mt-2 w-52 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 py-2 z-50 animate-in fade-in slide-in-from-top-1">
                                        <div className="px-4 py-2 border-b border-slate-100 dark:border-slate-800">
                                            <p className="text-xs font-black text-slate-400 uppercase tracking-wider">
                                                {user.role.replace("ROLE_", "")}
                                            </p>
                                            <p className="text-sm font-bold truncate">{user.email}</p>
                                        </div>
                                        <button
                                            onClick={() => { setUserMenuOpen(false); navigate("/me"); }}
                                            className="flex items-center gap-2 w-full px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                                        >
                                            <span className="material-symbols-outlined !text-base">account_circle</span>
                                            My Profile
                                        </button>
                                        <button
                                            onClick={() => { setUserMenuOpen(false); navigate("/settings"); }}
                                            className="flex items-center gap-2 w-full px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                                        >
                                            <span className="material-symbols-outlined !text-base">settings</span>
                                            Settings
                                        </button>
                                        {(user.role === "ROLE_ATHLETE" || user.role === "ROLE_GUIDE") && (
                                            <>
                                                <button
                                                    onClick={() => { setUserMenuOpen(false); navigate("/me/routes"); }}
                                                    className="flex items-center gap-2 w-full px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                                                >
                                                    <span className="material-symbols-outlined !text-base">route</span>
                                                    Mis rutas
                                                </button>
                                                <button
                                                    onClick={() => { setUserMenuOpen(false); navigate("/routes/new"); }}
                                                    className="flex items-center gap-2 w-full px-4 py-2 text-sm font-semibold text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/20 transition-colors"
                                                >
                                                    <span className="material-symbols-outlined !text-base">upload</span>
                                                    Subir ruta
                                                </button>
                                            </>
                                        )}
                                        {user.role === "ROLE_BUSINESS" && (
                                            <>
                                                <button
                                                    onClick={() => { setUserMenuOpen(false); navigate("/me"); }}
                                                    className="flex items-center gap-2 w-full px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                                                >
                                                    <span className="material-symbols-outlined !text-base">store</span>
                                                    Mi negocio
                                                </button>
                                                <button
                                                    onClick={() => { setUserMenuOpen(false); navigate("/me/offers"); }}
                                                    className="flex items-center gap-2 w-full px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                                                >
                                                    <span className="material-symbols-outlined !text-base">sell</span>
                                                    Mis ofertas
                                                </button>
                                                <button
                                                    onClick={() => { setUserMenuOpen(false); navigate("/offers/new"); }}
                                                    className="flex items-center gap-2 w-full px-4 py-2 text-sm font-semibold text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/20 transition-colors"
                                                >
                                                    <span className="material-symbols-outlined !text-base">add_circle</span>
                                                    Nueva oferta
                                                </button>
                                            </>
                                        )}
                                        <div className="h-px bg-slate-100 dark:bg-slate-800 my-1" />
                                        <button
                                            onClick={handleLogout}
                                            className="flex items-center gap-2 w-full px-4 py-2 text-sm font-semibold text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 transition-colors"
                                        >
                                            <span className="material-symbols-outlined !text-base">logout</span>
                                            Sign out
                                        </button>
                                    </div>
                                )}
                            </div>
                        ) : (
                            <>
                                <button
                                    onClick={() => navigate("/auth?mode=login")}
                                    className="px-4 py-2 text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all"
                                >
                                    Login
                                </button>
                                <button
                                    onClick={() => navigate("/auth?mode=register")}
                                    className="px-5 py-2.5 text-sm font-black text-white bg-primary hover:bg-blue-600 rounded-xl shadow-lg shadow-primary/20 transition-all active:scale-95"
                                >
                                    Sign Up
                                </button>
                            </>
                        )}
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
                    {isAuthenticated && user ? (
                        <div className="flex flex-col gap-2">
                            <div className="flex items-center gap-3 px-2">
                                <div className="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center text-primary font-black">
                                    {(user.name ?? user.email).charAt(0).toUpperCase()}
                                </div>
                                <div>
                                    <p className="text-sm font-bold">{user.name ?? user.email}</p>
                                    <p className="text-xs text-slate-400">{user.role.replace("ROLE_", "")}</p>
                                </div>
                            </div>
                            {(user.role === "ROLE_ATHLETE" || user.role === "ROLE_GUIDE") && (
                                <>
                                    <button
                                        onClick={() => { setMobileMenuOpen(false); navigate("/me/routes"); }}
                                        className="w-full py-3 text-center font-bold text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded-xl"
                                    >
                                        Mis rutas
                                    </button>
                                    <button
                                        onClick={() => { setMobileMenuOpen(false); navigate("/routes/new"); }}
                                        className="w-full py-3 text-center font-bold text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 rounded-xl"
                                    >
                                        Subir ruta
                                    </button>
                                </>
                            )}
                            {user.role === "ROLE_BUSINESS" && (
                                <>
                                    <button
                                        onClick={() => { setMobileMenuOpen(false); navigate("/me"); }}
                                        className="w-full py-3 text-center font-bold text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded-xl"
                                    >
                                        Mi negocio
                                    </button>
                                    <button
                                        onClick={() => { setMobileMenuOpen(false); navigate("/me/offers"); }}
                                        className="w-full py-3 text-center font-bold text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded-xl"
                                    >
                                        Mis ofertas
                                    </button>
                                    <button
                                        onClick={() => { setMobileMenuOpen(false); navigate("/offers/new"); }}
                                        className="w-full py-3 text-center font-bold text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 rounded-xl"
                                    >
                                        Nueva oferta
                                    </button>
                                </>
                            )}
                            <button
                                onClick={handleLogout}
                                className="w-full py-3 text-center font-bold text-red-500 border border-red-200 dark:border-red-800 rounded-xl"
                            >
                                Sign out
                            </button>
                        </div>
                    ) : (
                        <>
                            <button
                                onClick={() => { setMobileMenuOpen(false); navigate("/auth?mode=login"); }}
                                className="w-full py-3 text-center font-bold text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded-xl"
                            >
                                Login
                            </button>
                            <button
                                onClick={() => { setMobileMenuOpen(false); navigate("/auth?mode=register"); }}
                                className="w-full py-3 text-center font-bold text-white bg-primary rounded-xl shadow-lg"
                            >
                                Sign Up
                            </button>
                        </>
                    )}
                </div>
            )}
        </header>
    );
}
