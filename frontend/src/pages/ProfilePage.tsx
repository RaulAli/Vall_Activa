import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { useAuthStore } from "../store/authStore";
import { updateMe, getMe } from "../features/user/api/userApi";
import { HttpError } from "../shared/api/http";

const ROLE_LABEL: Record<string, string> = {
    ROLE_BUSINESS: "Business",
    ROLE_ATHLETE: "Athlete",
    ROLE_GUIDE: "Guide",
};

const ROLE_COLOR: Record<string, string> = {
    ROLE_BUSINESS: "bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300",
    ROLE_ATHLETE: "bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300",
    ROLE_GUIDE: "bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300",
};

const SPORT_OPTIONS = [
    { code: "hike", label: "Hiking" },
    { code: "bike", label: "Cycling" },
    { code: "run", label: "Running" },
    { code: "ski", label: "Skiing" },
    { code: "climb", label: "Climbing" },
    { code: "kayak", label: "Kayaking" },
    { code: "surf", label: "Surfing" },
    { code: "swim", label: "Swimming" },
];

export function ProfilePage() {
    const navigate = useNavigate();
    const { isAuthenticated, user, token, setUser } = useAuthStore();

    // Redirect if not authenticated
    useEffect(() => {
        if (!isAuthenticated) navigate("/auth", { replace: true });
    }, [isAuthenticated, navigate]);

    const [isEditing, setIsEditing] = useState(false);
    const [isSaving, setIsSaving] = useState(false);
    const [saveError, setSaveError] = useState<string | null>(null);
    const [saveSuccess, setSaveSuccess] = useState(false);

    // Form state
    const [name, setName] = useState(user?.name ?? "");
    const [slug, setSlug] = useState(user?.slug ?? "");
    const [avatar, setAvatar] = useState(user?.avatar ?? "");
    // Business
    const [lat, setLat] = useState<string>(user?.lat != null ? String(user.lat) : "");
    const [lng, setLng] = useState<string>(user?.lng != null ? String(user.lng) : "");
    // Athlete / Guide
    const [city, setCity] = useState(user?.city ?? "");
    const [birthDate, setBirthDate] = useState(user?.birthDate ?? "");
    // Guide
    const [bio, setBio] = useState(user?.bio ?? "");
    const [sports, setSports] = useState<string[]>(user?.sports ?? []);

    if (!user || !isAuthenticated) return null;

    const role = user.role;
    const isBusiness = role === "ROLE_BUSINESS";
    const isAthlete = role === "ROLE_ATHLETE";
    const isGuide = role === "ROLE_GUIDE";

    const resetForm = () => {
        setName(user.name ?? "");
        setSlug(user.slug ?? "");
        setAvatar(user.avatar ?? "");
        setLat(user.lat != null ? String(user.lat) : "");
        setLng(user.lng != null ? String(user.lng) : "");
        setCity(user.city ?? "");
        setBirthDate(user.birthDate ?? "");
        setBio(user.bio ?? "");
        setSports(user.sports ?? []);
        setSaveError(null);
    };

    const handleCancel = () => {
        resetForm();
        setIsEditing(false);
    };

    const handleSave = async () => {
        if (!token) return;
        setSaveError(null);
        setSaveSuccess(false);
        setIsSaving(true);

        try {
            const payload: Record<string, unknown> = {
                name: name.trim(),
                slug: slug.trim(),
            };
            if (avatar.trim()) payload.avatar = avatar.trim();
            else payload.avatar = null;

            if (isBusiness) {
                payload.lat = lat !== "" ? parseFloat(lat) : null;
                payload.lng = lng !== "" ? parseFloat(lng) : null;
            }
            if (isAthlete) {
                payload.city = city.trim() || null;
                payload.birthDate = birthDate || null;
            }
            if (isGuide) {
                payload.bio = bio.trim() || null;
                payload.city = city.trim() || null;
                payload.lat = lat !== "" ? parseFloat(lat) : null;
                payload.lng = lng !== "" ? parseFloat(lng) : null;
                payload.sports = sports;
            }

            await updateMe(token, payload as any);

            // Refresh store user
            const updated = await getMe(token);
            setUser(updated);

            setSaveSuccess(true);
            setIsEditing(false);
            setTimeout(() => setSaveSuccess(false), 3000);
        } catch (err) {
            if (err instanceof HttpError) {
                const body = err.body as Record<string, unknown>;
                const code = body.error as string | undefined;
                if (code === "slug_already_taken") setSaveError("That slug is already taken.");
                else setSaveError("Failed to save changes. Please try again.");
            } else {
                setSaveError("Failed to save changes. Please try again.");
            }
        } finally {
            setIsSaving(false);
        }
    };

    const toggleSport = (code: string) => {
        setSports((prev) =>
            prev.includes(code) ? prev.filter((s) => s !== code) : [...prev, code]
        );
    };

    const inputClass = (editable = true) =>
        `w-full rounded-xl border h-12 px-4 text-sm font-medium focus:outline-none focus:ring-2 transition-all bg-white dark:bg-[#1a2333] ${editable && isEditing
            ? "border-slate-300 dark:border-slate-600 focus:ring-primary/50 focus:border-primary"
            : "border-transparent bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 cursor-default"
        }`;

    return (
        <div className="min-h-screen bg-slate-50 dark:bg-background-dark">
            {/* Header bar */}
            <div className="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
                <div className="max-w-3xl mx-auto px-4 sm:px-6 py-4 flex items-center gap-3">
                    <button
                        onClick={() => navigate(-1)}
                        className="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-slate-500 dark:text-slate-400"
                    >
                        <span className="material-symbols-outlined">arrow_back</span>
                    </button>
                    <h1 className="text-lg font-black text-slate-900 dark:text-white">My Profile</h1>
                </div>
            </div>

            <div className="max-w-3xl mx-auto px-4 sm:px-6 py-8 space-y-6">

                {/* Save feedback */}
                {saveSuccess && (
                    <div className="flex items-center gap-2 px-4 py-3 bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-800 rounded-xl text-sm text-green-700 dark:text-green-400 font-semibold">
                        <span className="material-symbols-outlined !text-base">check_circle</span>
                        Profile updated successfully.
                    </div>
                )}
                {saveError && (
                    <div className="flex items-center gap-2 px-4 py-3 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-600 dark:text-red-400 font-semibold">
                        <span className="material-symbols-outlined !text-base">error</span>
                        {saveError}
                    </div>
                )}

                {/* Profile card */}
                <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    {/* Avatar + identity */}
                    <div className="flex items-center gap-5 p-6 border-b border-slate-100 dark:border-slate-800">
                        <div className="relative shrink-0">
                            <div className="w-20 h-20 rounded-2xl bg-primary/10 flex items-center justify-center text-primary font-black text-3xl overflow-hidden ring-4 ring-white dark:ring-slate-900 shadow-md">
                                {(isEditing ? avatar : user.avatar) ? (
                                    <img
                                        src={isEditing ? avatar : (user.avatar ?? "")}
                                        alt={user.name ?? ""}
                                        className="w-full h-full object-cover"
                                        onError={(e) => { (e.target as HTMLImageElement).style.display = "none"; }}
                                    />
                                ) : (
                                    <span>{(user.name ?? user.email).charAt(0).toUpperCase()}</span>
                                )}
                            </div>
                        </div>
                        <div className="flex-1 min-w-0">
                            <div className="flex items-center gap-2 flex-wrap">
                                <h2 className="text-xl font-black text-slate-900 dark:text-white truncate">
                                    {user.name ?? user.email}
                                </h2>
                                <span className={`text-xs font-black px-2.5 py-1 rounded-full ${ROLE_COLOR[role] ?? ""}`}>
                                    {ROLE_LABEL[role] ?? role}
                                </span>
                            </div>
                            <p className="text-sm text-slate-500 dark:text-slate-400 mt-0.5 truncate">{user.email}</p>
                            {user.slug && (
                                <p className="text-xs text-slate-400 dark:text-slate-500 mt-0.5 font-mono">@{user.slug}</p>
                            )}
                        </div>
                        <div className="shrink-0">
                            {!isEditing ? (
                                <button
                                    onClick={() => setIsEditing(true)}
                                    className="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary text-white text-sm font-bold hover:bg-primary/90 transition-all shadow-sm"
                                >
                                    <span className="material-symbols-outlined !text-base">edit</span>
                                    Edit
                                </button>
                            ) : (
                                <div className="flex items-center gap-2">
                                    <button
                                        onClick={handleCancel}
                                        disabled={isSaving}
                                        className="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all disabled:opacity-50"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        onClick={handleSave}
                                        disabled={isSaving}
                                        className="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary text-white text-sm font-bold hover:bg-primary/90 transition-all shadow-sm disabled:opacity-60"
                                    >
                                        {isSaving ? (
                                            <span className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                                        ) : (
                                            <span className="material-symbols-outlined !text-base">save</span>
                                        )}
                                        Save
                                    </button>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Fields */}
                    <div className="p-6 space-y-5">
                        {/* Base fields */}
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                    Display Name
                                </label>
                                <input
                                    type="text"
                                    value={isEditing ? name : (user.name ?? "")}
                                    onChange={(e) => setName(e.target.value)}
                                    disabled={!isEditing}
                                    className={inputClass()}
                                    placeholder="Your name"
                                />
                            </div>
                            <div>
                                <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                    Slug
                                </label>
                                <div className="relative">
                                    <span className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-mono">@</span>
                                    <input
                                        type="text"
                                        value={isEditing ? slug : (user.slug ?? "")}
                                        onChange={(e) => setSlug(e.target.value.toLowerCase().replace(/[^a-z0-9\-_]/g, ""))}
                                        disabled={!isEditing}
                                        className={`${inputClass()} pl-8`}
                                        placeholder="your-slug"
                                    />
                                </div>
                            </div>
                        </div>

                        {/* Avatar URL */}
                        <div>
                            <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                Avatar URL
                            </label>
                            <input
                                type="url"
                                value={isEditing ? avatar : (user.avatar ?? "")}
                                onChange={(e) => setAvatar(e.target.value)}
                                disabled={!isEditing}
                                className={inputClass()}
                                placeholder="https://example.com/avatar.jpg"
                            />
                        </div>

                        {/* Business fields */}
                        {isBusiness && (
                            <div>
                                <p className="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Location</p>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-xs text-slate-400 mb-1.5">Latitude</label>
                                        <input
                                            type="number"
                                            step="any"
                                            value={isEditing ? lat : (user.lat != null ? String(user.lat) : "")}
                                            onChange={(e) => setLat(e.target.value)}
                                            disabled={!isEditing}
                                            className={inputClass()}
                                            placeholder="40.4168"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-xs text-slate-400 mb-1.5">Longitude</label>
                                        <input
                                            type="number"
                                            step="any"
                                            value={isEditing ? lng : (user.lng != null ? String(user.lng) : "")}
                                            onChange={(e) => setLng(e.target.value)}
                                            disabled={!isEditing}
                                            className={inputClass()}
                                            placeholder="-3.7038"
                                        />
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* Athlete fields */}
                        {isAthlete && (
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">City</label>
                                    <input
                                        type="text"
                                        value={isEditing ? city : (user.city ?? "")}
                                        onChange={(e) => setCity(e.target.value)}
                                        disabled={!isEditing}
                                        className={inputClass()}
                                        placeholder="Madrid"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Birth Date</label>
                                    <input
                                        type="date"
                                        value={isEditing ? birthDate : (user.birthDate ?? "")}
                                        onChange={(e) => setBirthDate(e.target.value)}
                                        disabled={!isEditing}
                                        className={inputClass()}
                                    />
                                </div>
                            </div>
                        )}

                        {/* Guide fields */}
                        {isGuide && (
                            <div className="space-y-4">
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">City</label>
                                        <input
                                            type="text"
                                            value={isEditing ? city : (user.city ?? "")}
                                            onChange={(e) => setCity(e.target.value)}
                                            disabled={!isEditing}
                                            className={inputClass()}
                                            placeholder="Madrid"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Bio</label>
                                    <textarea
                                        value={isEditing ? bio : (user.bio ?? "")}
                                        onChange={(e) => setBio(e.target.value)}
                                        disabled={!isEditing}
                                        rows={3}
                                        className={`w-full rounded-xl border px-4 py-3 text-sm font-medium focus:outline-none focus:ring-2 transition-all bg-white dark:bg-[#1a2333] resize-none ${isEditing
                                                ? "border-slate-300 dark:border-slate-600 focus:ring-primary/50 focus:border-primary"
                                                : "border-transparent bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 cursor-default"
                                            }`}
                                        placeholder="Tell us about yourself..."
                                    />
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-xs text-slate-400 mb-1.5">Latitude</label>
                                        <input
                                            type="number"
                                            step="any"
                                            value={isEditing ? lat : (user.lat != null ? String(user.lat) : "")}
                                            onChange={(e) => setLat(e.target.value)}
                                            disabled={!isEditing}
                                            className={inputClass()}
                                            placeholder="40.4168"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-xs text-slate-400 mb-1.5">Longitude</label>
                                        <input
                                            type="number"
                                            step="any"
                                            value={isEditing ? lng : (user.lng != null ? String(user.lng) : "")}
                                            onChange={(e) => setLng(e.target.value)}
                                            disabled={!isEditing}
                                            className={inputClass()}
                                            placeholder="-3.7038"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">
                                        Sports
                                    </label>
                                    <div className="flex flex-wrap gap-2">
                                        {SPORT_OPTIONS.map((s) => {
                                            const active = (isEditing ? sports : (user.sports ?? [])).includes(s.code);
                                            return (
                                                <button
                                                    key={s.code}
                                                    type="button"
                                                    disabled={!isEditing}
                                                    onClick={() => toggleSport(s.code)}
                                                    className={`px-3 py-1.5 rounded-full text-xs font-bold border transition-all ${active
                                                            ? "bg-primary text-white border-primary"
                                                            : "bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-primary/50"
                                                        } disabled:cursor-default`}
                                                >
                                                    {s.label}
                                                </button>
                                            );
                                        })}
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                {/* Read-only account info */}
                <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <h3 className="text-sm font-black text-slate-900 dark:text-white mb-4">Account Info</h3>
                    <div className="space-y-3">
                        <div className="flex justify-between items-center py-2.5 border-b border-slate-100 dark:border-slate-800">
                            <span className="text-sm text-slate-500 dark:text-slate-400">Email</span>
                            <span className="text-sm font-semibold text-slate-900 dark:text-white">{user.email}</span>
                        </div>
                        <div className="flex justify-between items-center py-2.5 border-b border-slate-100 dark:border-slate-800">
                            <span className="text-sm text-slate-500 dark:text-slate-400">Role</span>
                            <span className={`text-xs font-black px-2.5 py-1 rounded-full ${ROLE_COLOR[role] ?? ""}`}>
                                {ROLE_LABEL[role] ?? role}
                            </span>
                        </div>
                        <div className="flex justify-between items-center py-2.5">
                            <span className="text-sm text-slate-500 dark:text-slate-400">Member since</span>
                            <span className="text-sm font-semibold text-slate-900 dark:text-white">
                                {new Date(user.createdAt).toLocaleDateString("en-GB", { day: "numeric", month: "long", year: "numeric" })}
                            </span>
                        </div>
                    </div>
                </div>

                {/* Quick links */}
                <div className="flex gap-3">
                    {user.slug && (
                        <button
                            onClick={() => navigate(`/profile/${user.slug}`)}
                            className="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all"
                        >
                            <span className="material-symbols-outlined !text-base">person</span>
                            View public profile
                        </button>
                    )}
                    <button
                        onClick={() => navigate("/settings")}
                        className="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all"
                    >
                        <span className="material-symbols-outlined !text-base">settings</span>
                        Security settings
                    </button>
                </div>
            </div>
        </div>
    );
}
