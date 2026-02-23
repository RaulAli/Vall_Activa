import { useState, useEffect } from "react";
import { useNavigate, useSearchParams } from "react-router-dom";
import { useAuthStore } from "../store/authStore";
import { login, register, getMe } from "../features/auth/api/authApi";
import { HttpError } from "../shared/api/http";
import type { AuthRole } from "../features/auth/domain/types";

type Mode = "login" | "register";

const ROLE_LABELS: { value: AuthRole; label: string; icon: string }[] = [
    { value: "ROLE_ATHLETE", label: "Athlete", icon: "directions_run" },
    { value: "ROLE_GUIDE", label: "Guide", icon: "hiking" },
    { value: "ROLE_BUSINESS", label: "Business", icon: "storefront" },
];

function slugify(value: string): string {
    return value
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/[^a-z0-9\s-]/g, "")
        .trim()
        .replace(/\s+/g, "-")
        .replace(/-+/g, "-");
}

export function AuthPage() {
    const navigate = useNavigate();
    const [searchParams] = useSearchParams();
    const { setAuth, isAuthenticated } = useAuthStore();

    const initialMode: Mode = searchParams.get("mode") === "register" ? "register" : "login";
    const [mode, setMode] = useState<Mode>(initialMode);

    // Form fields
    const [name, setName] = useState("");
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [role, setRole] = useState<AuthRole>("ROLE_ATHLETE");
    const [slug, setSlug] = useState("");
    const [slugManuallyEdited, setSlugManuallyEdited] = useState(false);

    // UI state
    const [showPassword, setShowPassword] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [successMessage, setSuccessMessage] = useState<string | null>(null);
    const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});

    // Redirect if already authenticated
    useEffect(() => {
        if (isAuthenticated) navigate("/", { replace: true });
    }, [isAuthenticated, navigate]);

    // Auto-generate slug from name
    useEffect(() => {
        if (!slugManuallyEdited && mode === "register") {
            setSlug(slugify(name));
        }
    }, [name, mode, slugManuallyEdited]);

    const switchMode = (m: Mode) => {
        setMode(m);
        setError(null);
        setSuccessMessage(null);
        setFieldErrors({});
        setName("");
        setEmail("");
        setPassword("");
        setSlug("");
        setSlugManuallyEdited(false);
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setError(null);
        setSuccessMessage(null);
        setFieldErrors({});

        // Client-side validation
        if (mode === "register") {
            const fe: Record<string, string> = {};
            if (name.trim().length < 2) fe.name = "Name must be at least 2 characters.";
            if (password.length < 8) fe.password = "Password must be at least 8 characters.";
            if (password.length > 72) fe.password = "Password cannot exceed 72 characters.";
            if (slug.trim().length < 3) fe.slug = "Slug must be at least 3 characters.";
            if (!/^[a-z0-9\-_]+$/i.test(slug.trim())) fe.slug = "Slug can only contain letters, numbers, hyphens and underscores.";
            if (Object.keys(fe).length > 0) { setFieldErrors(fe); return; }
        }

        setIsLoading(true);

        try {
            if (mode === "login") {
                const res = await login({ email: email.trim(), password });
                const user = await getMe(res.accessToken);
                setAuth(res.accessToken, user);
                navigate("/", { replace: true });
            } else {
                await register({
                    email: email.trim(),
                    password,
                    role,
                    name: name.trim(),
                    slug: slug.trim(),
                });
                // Register succeeded — switch to login with email pre-filled
                const registeredEmail = email.trim();
                setMode("login");
                setName("");
                setPassword("");
                setSlug("");
                setSlugManuallyEdited(false);
                setFieldErrors({});
                setEmail(registeredEmail);
                setSuccessMessage("Account created! Sign in to continue.");
            }
        } catch (err: unknown) {
            if (err instanceof HttpError) {
                const body = err.body as Record<string, unknown>;
                const errorCode = body.error as string | undefined;

                if (err.status === 401) {
                    setError("Incorrect email or password. Please try again.");
                } else if (err.status === 409) {
                    if (errorCode === "email_already_taken") {
                        setFieldErrors({ email: "This email is already registered." });
                    } else if (errorCode === "slug_already_taken") {
                        setFieldErrors({ slug: "This slug is already taken. Choose another." });
                    } else {
                        setError("Account already exists.");
                    }
                } else if (err.status === 422) {
                    const fields = body.fields as Record<string, string[]> | undefined;
                    if (fields) {
                        const fe: Record<string, string> = {};
                        for (const [field, msgs] of Object.entries(fields)) {
                            fe[field] = msgs[0];
                        }
                        setFieldErrors(fe);
                    } else {
                        setError("Please check the form and try again.");
                    }
                } else {
                    setError("Something went wrong. Please try again.");
                }
            } else {
                setError("Network error. Please check your connection.");
            }
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div className="relative flex min-h-screen w-full flex-col overflow-x-hidden bg-[#f6f6f8] dark:bg-[#101622] font-display text-[#0d121b] dark:text-white">
            <div className="flex h-full grow flex-col">
                {/* Top Navigation Bar */}
                <header className="flex items-center justify-between whitespace-nowrap border-b border-[#e7ebf3] dark:border-[#2a3447] px-6 md:px-10 py-3 bg-[#f6f6f8] dark:bg-[#101622]">
                    <button
                        onClick={() => navigate("/")}
                        className="flex items-center gap-3 group"
                    >
                        <div className="size-8 bg-primary/10 rounded-xl flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-all duration-300">
                            <span className="material-symbols-outlined !text-xl font-bold">explore</span>
                        </div>
                        <h2 className="text-lg font-bold leading-tight tracking-[-0.015em]">Vall Activa</h2>
                    </button>
                    <div className="hidden md:flex items-center gap-9">
                        <button onClick={() => navigate("/")} className="text-sm font-medium leading-normal hover:text-primary transition-colors">
                            Explore
                        </button>
                        <button onClick={() => navigate("/routes")} className="text-sm font-medium leading-normal hover:text-primary transition-colors">
                            Routes
                        </button>
                        <button onClick={() => navigate("/offers")} className="text-sm font-medium leading-normal hover:text-primary transition-colors">
                            Offers
                        </button>
                    </div>
                </header>

                <main className="flex flex-1 items-stretch overflow-hidden">
                    {/* Left Side: Visual */}
                    <div className="hidden lg:flex lg:w-1/2 relative">
                        <div
                            className="absolute inset-0 bg-center bg-no-repeat bg-cover"
                            style={{
                                backgroundImage: `url("https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&q=80&w=2070")`,
                            }}
                        />
                        <div className="absolute inset-0 bg-gradient-to-tr from-[#101622]/70 to-transparent flex items-end p-20">
                            <div className="text-white max-w-md">
                                <h1 className="text-4xl font-extrabold mb-4 leading-tight">
                                    Discover the path less traveled.
                                </h1>
                                <p className="text-lg opacity-90 leading-relaxed">
                                    Join the world's premier marketplace for hikers, climbers, and outdoor enthusiasts.
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Right Side: Form */}
                    <div className="w-full lg:w-1/2 flex flex-col items-center justify-center p-6 md:p-12 lg:p-24 overflow-y-auto bg-[#f6f6f8] dark:bg-[#101622]">
                        <div className="w-full max-w-[440px] flex flex-col gap-6">
                            {/* Header */}
                            <div className="flex flex-col gap-1">
                                <h2 className="text-3xl font-bold leading-tight tracking-[-0.015em]">
                                    {mode === "login" ? "Welcome back" : "Create an account"}
                                </h2>
                                <p className="text-[#4c669a] dark:text-slate-400 text-base">
                                    {mode === "login"
                                        ? "Enter your details to access your account"
                                        : "Join the Vall Activa community today"}
                                </p>
                            </div>

                            {/* Segmented Toggle */}
                            <div className="flex py-1">
                                <div className="flex h-12 flex-1 items-center justify-center rounded-xl bg-[#e7ebf3] dark:bg-[#2a3447] p-1.5 gap-1">
                                    {(["login", "register"] as Mode[]).map((m) => (
                                        <button
                                            key={m}
                                            type="button"
                                            onClick={() => switchMode(m)}
                                            className={`flex-1 h-full rounded-lg text-sm font-semibold leading-normal transition-all ${mode === m
                                                ? "bg-white dark:bg-[#1a2333] shadow-sm text-[#0d121b] dark:text-white"
                                                : "text-[#4c669a] hover:text-[#0d121b] dark:hover:text-white"
                                                }`}
                                        >
                                            {m === "login" ? "Login" : "Sign Up"}
                                        </button>
                                    ))}
                                </div>
                            </div>

                            {/* Form */}
                            <form onSubmit={handleSubmit} className="flex flex-col gap-4">
                                {/* Name (register only) */}
                                {mode === "register" && (
                                    <div className="flex flex-col w-full">
                                        <label className="text-sm font-semibold leading-normal pb-2">
                                            Full Name
                                        </label>
                                        <input
                                            type="text"
                                            autoComplete="name"
                                            value={name}
                                            onChange={(e) => { setName(e.target.value); setFieldErrors(p => ({ ...p, name: "" })); }}
                                            placeholder="John Doe"
                                            required
                                            minLength={2}
                                            className={`flex w-full rounded-xl border h-14 px-4 text-base font-normal placeholder:text-[#4c669a] focus:outline-none focus:ring-2 transition-all bg-white dark:bg-[#1a2333] ${fieldErrors.name ? "border-red-400 focus:ring-red-300" : "border-[#cfd7e7] dark:border-[#2a3447] focus:ring-primary/50"}`}
                                        />
                                        {fieldErrors.name && <p className="mt-1.5 text-xs text-red-500 font-semibold">{fieldErrors.name}</p>}
                                    </div>
                                )}

                                {/* Email */}
                                <div className="flex flex-col w-full">
                                    <label className="text-sm font-semibold leading-normal pb-2">
                                        Email address
                                    </label>
                                    <input
                                        type="email"
                                        autoComplete="email"
                                        value={email}
                                        onChange={(e) => { setEmail(e.target.value); setFieldErrors(p => ({ ...p, email: "" })); }}
                                        placeholder="name@example.com"
                                        required
                                        className={`flex w-full rounded-xl border h-14 px-4 text-base font-normal placeholder:text-[#4c669a] focus:outline-none focus:ring-2 transition-all bg-white dark:bg-[#1a2333] ${fieldErrors.email ? "border-red-400 focus:ring-red-300" : "border-[#cfd7e7] dark:border-[#2a3447] focus:ring-primary/50"}`}
                                    />
                                    {fieldErrors.email && <p className="mt-1.5 text-xs text-red-500 font-semibold">{fieldErrors.email}</p>}
                                </div>

                                {/* Password */}
                                <div className="flex flex-col w-full">
                                    <div className="flex justify-between items-center pb-2">
                                        <label className="text-sm font-semibold leading-normal">
                                            Password
                                        </label>
                                        {mode === "login" && (
                                            <button
                                                type="button"
                                                className="text-xs text-primary font-semibold hover:underline"
                                            >
                                                Forgot?
                                            </button>
                                        )}
                                    </div>
                                    {mode === "register" && (
                                        <p className="text-xs text-[#4c669a] mb-2">Minimum 8 characters, maximum 72.</p>
                                    )}
                                    <div className="relative">
                                        <input
                                            type={showPassword ? "text" : "password"}
                                            autoComplete={mode === "login" ? "current-password" : "new-password"}
                                            value={password}
                                            onChange={(e) => { setPassword(e.target.value); setFieldErrors(p => ({ ...p, password: "" })); }}
                                            placeholder="••••••••"
                                            required
                                            minLength={mode === "register" ? 8 : 1}
                                            className={`flex w-full rounded-xl border h-14 px-4 pr-12 text-base font-normal placeholder:text-[#4c669a] focus:outline-none focus:ring-2 transition-all bg-white dark:bg-[#1a2333] ${fieldErrors.password ? "border-red-400 focus:ring-red-300" : "border-[#cfd7e7] dark:border-[#2a3447] focus:ring-primary/50"}`}
                                        />
                                        <button
                                            type="button"
                                            onClick={() => setShowPassword((v) => !v)}
                                            className="absolute right-4 top-1/2 -translate-y-1/2 text-[#4c669a] hover:text-primary transition-colors"
                                        >
                                            <span className="material-symbols-outlined text-[20px]">
                                                {showPassword ? "visibility_off" : "visibility"}
                                            </span>
                                        </button>
                                    </div>
                                    {fieldErrors.password && <p className="mt-1.5 text-xs text-red-500 font-semibold">{fieldErrors.password}</p>}
                                </div>

                                {/* Role (register only) */}
                                {mode === "register" && (
                                    <div className="flex flex-col w-full">
                                        <label className="text-sm font-semibold leading-normal pb-2">
                                            I am a...
                                        </label>
                                        <div className="grid grid-cols-3 gap-2">
                                            {ROLE_LABELS.map(({ value, label, icon }) => (
                                                <button
                                                    key={value}
                                                    type="button"
                                                    onClick={() => setRole(value)}
                                                    className={`flex flex-col items-center gap-1.5 py-3 px-2 rounded-xl border text-sm font-semibold transition-all ${role === value
                                                        ? "border-primary bg-primary/10 text-primary ring-2 ring-primary/20"
                                                        : "border-[#cfd7e7] dark:border-[#2a3447] bg-white dark:bg-[#1a2333] text-[#4c669a] hover:border-primary/50"
                                                        }`}
                                                >
                                                    <span className="material-symbols-outlined !text-2xl">{icon}</span>
                                                    {label}
                                                </button>
                                            ))}
                                        </div>
                                    </div>
                                )}

                                {/* Slug (register only) */}
                                {mode === "register" && (
                                    <div className="flex flex-col w-full">
                                        <label className="text-sm font-semibold leading-normal pb-2">
                                            Profile slug
                                            <span className="ml-2 text-xs font-normal text-[#4c669a]">
                                                your public URL
                                            </span>
                                        </label>
                                        <div className="relative">
                                            <span className="absolute left-4 top-1/2 -translate-y-1/2 text-[#4c669a] text-sm font-semibold select-none">
                                                vallactiva.com/
                                            </span>
                                            <input
                                                type="text"
                                                value={slug}
                                                onChange={(e) => {
                                                    setSlug(e.target.value.toLowerCase().replace(/[^a-z0-9-_]/g, ""));
                                                    setSlugManuallyEdited(true);
                                                    setFieldErrors(p => ({ ...p, slug: "" }));
                                                }}
                                                placeholder="my-profile"
                                                required
                                                minLength={3}
                                                className={`flex w-full rounded-xl border h-14 pl-[5.5rem] pr-4 text-base font-normal placeholder:text-[#4c669a] focus:outline-none focus:ring-2 transition-all bg-white dark:bg-[#1a2333] ${fieldErrors.slug ? "border-red-400 focus:ring-red-300" : "border-[#cfd7e7] dark:border-[#2a3447] focus:ring-primary/50"}`}
                                            />
                                        </div>
                                        {fieldErrors.slug && <p className="mt-1.5 text-xs text-red-500 font-semibold">{fieldErrors.slug}</p>}
                                    </div>
                                )}

                                {/* Success */}
                                {successMessage && (
                                    <div className="flex items-center gap-2 px-4 py-3 bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-800 rounded-xl text-sm text-green-700 dark:text-green-400 font-semibold">
                                        <span className="material-symbols-outlined !text-base">check_circle</span>
                                        {successMessage}
                                    </div>
                                )}

                                {/* Error */}
                                {error && (
                                    <div className="flex items-center gap-2 px-4 py-3 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-600 dark:text-red-400 font-semibold">
                                        <span className="material-symbols-outlined !text-base">error</span>
                                        {error}
                                    </div>
                                )}

                                {/* Submit Button */}
                                <button
                                    type="submit"
                                    disabled={isLoading}
                                    className="flex w-full items-center justify-center rounded-xl h-14 px-5 bg-primary text-white text-base font-bold leading-normal tracking-[0.015em] hover:bg-primary/90 transition-all shadow-lg shadow-primary/20 disabled:opacity-60 disabled:cursor-not-allowed active:scale-[0.98] mt-1"
                                >
                                    {isLoading ? (
                                        <span className="flex items-center gap-2">
                                            <svg className="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                            </svg>
                                            {mode === "login" ? "Signing in..." : "Creating account..."}
                                        </span>
                                    ) : (
                                        mode === "login" ? "Continue" : "Create account"
                                    )}
                                </button>
                            </form>

                            {/* Divider */}
                            <div className="flex items-center gap-4">
                                <div className="h-[1px] flex-1 bg-[#e7ebf3] dark:bg-[#2a3447]" />
                                <span className="text-xs text-[#4c669a] uppercase font-bold tracking-wider">
                                    or continue with
                                </span>
                                <div className="h-[1px] flex-1 bg-[#e7ebf3] dark:bg-[#2a3447]" />
                            </div>

                            {/* Social Buttons */}
                            <div className="flex flex-col sm:flex-row gap-3">
                                <button
                                    type="button"
                                    className="flex flex-1 items-center justify-center gap-2 border border-[#cfd7e7] dark:border-[#2a3447] rounded-xl h-12 bg-white dark:bg-[#1a2333] hover:bg-[#f8f9fc] dark:hover:bg-[#232d41] transition-colors"
                                >
                                    <svg className="w-5 h-5" viewBox="0 0 24 24">
                                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4" />
                                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853" />
                                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05" />
                                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335" />
                                    </svg>
                                    <span className="text-sm font-semibold">Google</span>
                                </button>
                                <button
                                    type="button"
                                    className="flex flex-1 items-center justify-center gap-2 border border-[#cfd7e7] dark:border-[#2a3447] rounded-xl h-12 bg-white dark:bg-[#1a2333] hover:bg-[#f8f9fc] dark:hover:bg-[#232d41] transition-colors"
                                >
                                    <svg className="w-5 h-5 fill-current" viewBox="0 0 384 512">
                                        <path d="M318.7 268.7c-.2-36.7 16.4-64.4 50-84.8-18.8-26.9-47.2-41.7-84.7-44.6-35.5-2.8-74.3 20.7-88.5 20.7-15 0-49.4-19.7-76.4-19.7C63.3 141.2 4 184.8 4 273.5q0 39.3 14.4 81.2c12.8 36.7 59 126.7 107.2 125.2 25.2-.6 43-17.9 75.8-17.9 31.8 0 48.3 17.9 76.4 17.9 48.6-.7 90.4-82.5 102.6-119.3-65.2-30.7-61.7-90-61.7-91.9zm-56.6-164.2c27.3-32.4 24.8-61.9 24-72.5-24.1 1.4-52 16.4-67.9 34.9-17.5 19.8-27.8 44.3-25.6 71.9 26.1 2 49.9-11.4 69.5-34.3z" />
                                    </svg>
                                    <span className="text-sm font-semibold">Apple</span>
                                </button>
                            </div>

                            {/* Footer */}
                            <div className="mt-2 text-center">
                                <p className="text-sm text-[#4c669a]">
                                    By continuing, you agree to our{" "}
                                    <button type="button" className="text-primary font-semibold hover:underline">
                                        Terms of Service
                                    </button>{" "}
                                    and{" "}
                                    <button type="button" className="text-primary font-semibold hover:underline">
                                        Privacy Policy
                                    </button>
                                    .
                                </p>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    );
}
