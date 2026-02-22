import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import { useAuthStore } from "../store/authStore";
import { updateMe, getMe } from "../features/user/api/userApi";
import { getPublicProfile, getMyFollowers, getMyFollowing } from "../features/user/api/profileApi";
import type { ProfileStub } from "../features/user/api/profileApi";
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

function ProfileAvatar({ src, name, size = "lg" }: { src?: string | null; name: string; size?: "sm" | "lg" }) {
    const dim = size === "lg" ? "w-16 h-16" : "w-10 h-10";
    const text = size === "lg" ? "text-2xl" : "text-base";
    return (
        <div className={`${dim} rounded-xl bg-primary/10 flex items-center justify-center overflow-hidden shrink-0`}>
            {src
                ? <img src={src} alt={name} className="w-full h-full object-cover" />
                : <span className={`${text} font-black text-primary`}>{name.charAt(0).toUpperCase()}</span>
            }
        </div>
    );
}

function PeopleList({ items, onNavigate }: { items: ProfileStub[]; onNavigate: (slug: string) => void }) {
    return (
        <div className="divide-y divide-slate-100 dark:divide-slate-800">
            {items.length === 0 && (
                <p className="text-sm text-slate-400 text-center py-8">No hay nadie aqui todavia.</p>
            )}
            {items.map(p => (
                <button
                    key={p.slug}
                    onClick={() => onNavigate(p.slug)}
                    className="w-full flex items-center gap-3 py-3 px-1 hover:bg-slate-50 dark:hover:bg-slate-800/50 rounded-xl transition-colors text-left"
                >
                    <ProfileAvatar src={p.avatar} name={p.name} size="sm" />
                    <div className="flex-1 min-w-0">
                        <p className="text-sm font-bold text-slate-900 dark:text-white truncate">{p.name}</p>
                        <p className="text-xs text-slate-500 dark:text-slate-400 font-mono">@{p.slug}</p>
                    </div>
                    <span className={`text-[10px] font-black px-2 py-1 rounded-full ${ROLE_COLOR[p.role] ?? "bg-slate-100 text-slate-600"}`}>
                        {ROLE_LABEL[p.role] ?? p.role}
                    </span>
                    <span className="material-symbols-outlined !text-sm text-slate-300 dark:text-slate-600">chevron_right</span>
                </button>
            ))}
        </div>
    );
}

export function ProfilePage() {
    const navigate = useNavigate();
    const queryClient = useQueryClient();
    const { isAuthenticated, user, token, setUser } = useAuthStore();

    useEffect(() => {
        if (!isAuthenticated) navigate("/auth", { replace: true });
    }, [isAuthenticated, navigate]);

    const [drawerOpen, setDrawerOpen] = useState(false);
    const [isSaving, setIsSaving] = useState(false);
    const [saveError, setSaveError] = useState<string | null>(null);
    const [saveSuccess, setSaveSuccess] = useState(false);

    const [name, setName] = useState(user?.name ?? "");
    const [slug, setSlug] = useState(user?.slug ?? "");
    const [avatar, setAvatar] = useState(user?.avatar ?? "");
    const [lat, setLat] = useState<string>(user?.lat != null ? String(user.lat) : "");
    const [lng, setLng] = useState<string>(user?.lng != null ? String(user.lng) : "");
    const [city, setCity] = useState(user?.city ?? "");
    const [birthDate, setBirthDate] = useState(user?.birthDate ?? "");
    const [bio, setBio] = useState(user?.bio ?? "");
    const [sports, setSports] = useState<string[]>(user?.sports ?? []);

    type CommunityTab = "followers" | "following" | null;
    const [communityTab, setCommunityTab] = useState<CommunityTab>(null);

    const { data: publicProfile } = useQuery({
        queryKey: ["profile", user?.slug],
        queryFn: () => getPublicProfile(user!.slug!, token),
        enabled: !!user?.slug,
        staleTime: 30_000,
    });

    const { data: followers = [], isLoading: loadingFollowers } = useQuery({
        queryKey: ["me", "followers"],
        queryFn: () => getMyFollowers(token!),
        enabled: communityTab === "followers" && !!token,
        staleTime: 30_000,
    });

    const { data: following = [], isLoading: loadingFollowing } = useQuery({
        queryKey: ["me", "following"],
        queryFn: () => getMyFollowing(token!),
        enabled: communityTab === "following" && !!token,
        staleTime: 30_000,
    });

    if (!user || !isAuthenticated) return null;

    const role = user.role;
    const isBusiness = role === "ROLE_BUSINESS";
    const isAthlete = role === "ROLE_ATHLETE";
    const isGuide = role === "ROLE_GUIDE";

    const openDrawer = () => {
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
        setDrawerOpen(true);
    };

    const closeDrawer = () => { setDrawerOpen(false); setSaveError(null); };

    const handleSave = async () => {
        if (!token) return;
        setSaveError(null);
        setIsSaving(true);
        try {
            const payload: Record<string, unknown> = { name: name.trim(), slug: slug.trim(), avatar: avatar.trim() || null };
            if (isBusiness) { payload.lat = lat !== "" ? parseFloat(lat) : null; payload.lng = lng !== "" ? parseFloat(lng) : null; }
            if (isAthlete) { payload.city = city.trim() || null; payload.birthDate = birthDate || null; }
            if (isGuide) { payload.bio = bio.trim() || null; payload.city = city.trim() || null; payload.lat = lat !== "" ? parseFloat(lat) : null; payload.lng = lng !== "" ? parseFloat(lng) : null; payload.sports = sports; }
            await updateMe(token, payload as any);
            const updated = await getMe(token);
            setUser(updated);
            queryClient.invalidateQueries({ queryKey: ["profile", slug] });
            setSaveSuccess(true);
            setDrawerOpen(false);
            setTimeout(() => setSaveSuccess(false), 3000);
        } catch (err) {
            if (err instanceof HttpError) {
                const code = (err.body as Record<string, unknown>).error as string | undefined;
                setSaveError(code === "slug_already_taken" ? "Ese slug ya esta en uso." : "Error al guardar. Intentalo de nuevo.");
            } else {
                setSaveError("Error al guardar. Intentalo de nuevo.");
            }
        } finally { setIsSaving(false); }
    };

    const toggleSport = (code: string) => setSports(prev => prev.includes(code) ? prev.filter(s => s !== code) : [...prev, code]);
    const toggleTab = (tab: CommunityTab) => setCommunityTab(prev => prev === tab ? null : tab);

    const inputClass = "w-full rounded-xl border border-slate-300 dark:border-slate-600 h-12 px-4 text-sm font-medium bg-white dark:bg-[#1a2333] focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all";

    return (
        <div className="min-h-screen bg-slate-50 dark:bg-background-dark">
            <div className="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
                <div className="max-w-2xl mx-auto px-4 sm:px-6 py-4 flex items-center gap-3">
                    <button onClick={() => navigate(-1)} className="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-slate-500 dark:text-slate-400">
                        <span className="material-symbols-outlined">arrow_back</span>
                    </button>
                    <h1 className="text-lg font-black text-slate-900 dark:text-white flex-1">Mi Perfil</h1>
                    <button onClick={openDrawer} className="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary text-white text-sm font-bold hover:bg-primary/90 transition-all shadow-sm">
                        <span className="material-symbols-outlined !text-base">edit</span>
                        Editar
                    </button>
                </div>
            </div>

            <div className="max-w-2xl mx-auto px-4 sm:px-6 py-8 space-y-5">
                {saveSuccess && (
                    <div className="flex items-center gap-2 px-4 py-3 bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-800 rounded-xl text-sm text-green-700 dark:text-green-400 font-semibold">
                        <span className="material-symbols-outlined !text-base">check_circle</span>
                        Perfil actualizado correctamente.
                    </div>
                )}

                <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div className="flex items-center gap-5 p-6">
                        <div className="w-20 h-20 rounded-2xl bg-primary/10 flex items-center justify-center text-primary font-black text-3xl overflow-hidden ring-4 ring-white dark:ring-slate-900 shadow-md shrink-0">
                            {user.avatar
                                ? <img src={user.avatar} alt={user.name ?? ""} className="w-full h-full object-cover" onError={e => { (e.target as HTMLImageElement).style.display = "none"; }} />
                                : <span>{(user.name ?? user.email).charAt(0).toUpperCase()}</span>
                            }
                        </div>
                        <div className="flex-1 min-w-0">
                            <div className="flex items-center gap-2 flex-wrap mb-0.5">
                                <h2 className="text-xl font-black text-slate-900 dark:text-white truncate">{user.name ?? user.email}</h2>
                                <span className={`text-xs font-black px-2.5 py-1 rounded-full ${ROLE_COLOR[role] ?? ""}`}>{ROLE_LABEL[role] ?? role}</span>
                            </div>
                            <p className="text-sm text-slate-500 dark:text-slate-400 truncate">{user.email}</p>
                            {user.slug && <p className="text-xs text-slate-400 dark:text-slate-500 font-mono mt-0.5">@{user.slug}</p>}
                        </div>
                    </div>

                    <div className="border-t border-slate-100 dark:border-slate-800 grid grid-cols-2 divide-x divide-slate-100 dark:divide-slate-800">
                        <button onClick={() => toggleTab("followers")} className={`py-4 flex flex-col items-center gap-0.5 transition-colors ${communityTab === "followers" ? "bg-primary/5 dark:bg-primary/10" : "hover:bg-slate-50 dark:hover:bg-slate-800/50"}`}>
                            <span className="text-2xl font-black text-slate-900 dark:text-white">{publicProfile?.followersCount ?? ""}</span>
                            <span className="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1">
                                Seguidores
                                <span className="material-symbols-outlined !text-xs">{communityTab === "followers" ? "expand_less" : "expand_more"}</span>
                            </span>
                        </button>
                        <button onClick={() => toggleTab("following")} className={`py-4 flex flex-col items-center gap-0.5 transition-colors ${communityTab === "following" ? "bg-primary/5 dark:bg-primary/10" : "hover:bg-slate-50 dark:hover:bg-slate-800/50"}`}>
                            <span className="text-2xl font-black text-slate-900 dark:text-white">{publicProfile?.followingCount ?? ""}</span>
                            <span className="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1">
                                Siguiendo
                                <span className="material-symbols-outlined !text-xs">{communityTab === "following" ? "expand_less" : "expand_more"}</span>
                            </span>
                        </button>
                    </div>

                    {communityTab && (
                        <div className="border-t border-slate-100 dark:border-slate-800 px-4 py-3">
                            <p className="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">
                                {communityTab === "followers" ? "Personas que te siguen" : "Personas que sigues"}
                            </p>
                            {(communityTab === "followers" ? loadingFollowers : loadingFollowing) ? (
                                <div className="flex justify-center py-6"><span className="w-6 h-6 border-2 border-primary/30 border-t-primary rounded-full animate-spin" /></div>
                            ) : (
                                <PeopleList items={communityTab === "followers" ? followers : following} onNavigate={s => navigate(`/profile/${s}`)} />
                            )}
                        </div>
                    )}
                </div>

                <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <h3 className="text-sm font-black text-slate-900 dark:text-white mb-4">Informacion de la cuenta</h3>
                    <div className="space-y-0">
                        <div className="flex justify-between items-center py-3 border-b border-slate-100 dark:border-slate-800">
                            <span className="text-sm text-slate-500 dark:text-slate-400">Email</span>
                            <span className="text-sm font-semibold text-slate-900 dark:text-white">{user.email}</span>
                        </div>
                        <div className="flex justify-between items-center py-3 border-b border-slate-100 dark:border-slate-800">
                            <span className="text-sm text-slate-500 dark:text-slate-400">Rol</span>
                            <span className={`text-xs font-black px-2.5 py-1 rounded-full ${ROLE_COLOR[role] ?? ""}`}>{ROLE_LABEL[role] ?? role}</span>
                        </div>
                        <div className="flex justify-between items-center py-3">
                            <span className="text-sm text-slate-500 dark:text-slate-400">Miembro desde</span>
                            <span className="text-sm font-semibold text-slate-900 dark:text-white">
                                {new Date(user.createdAt).toLocaleDateString("es-ES", { day: "numeric", month: "long", year: "numeric" })}
                            </span>
                        </div>
                    </div>
                </div>

                <div className="flex gap-3 flex-wrap">
                    {user.slug && (
                        <button onClick={() => navigate(`/profile/${user.slug}`)} className="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                            <span className="material-symbols-outlined !text-base">person</span>
                            Ver perfil publico
                        </button>
                    )}
                    <button onClick={() => navigate("/settings")} className="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                        <span className="material-symbols-outlined !text-base">lock</span>
                        Seguridad y contrasena
                    </button>
                </div>
            </div>

            {/* BACKDROP */}
            <div onClick={closeDrawer} className={`fixed inset-0 bg-black/40 backdrop-blur-sm z-[200] transition-opacity duration-300 ${drawerOpen ? "opacity-100 pointer-events-auto" : "opacity-0 pointer-events-none"}`} />

            {/* DRAWER */}
            <div className={`fixed inset-y-0 right-0 w-full sm:w-[500px] bg-white dark:bg-slate-900 shadow-2xl z-[210] flex flex-col transition-transform duration-300 ease-out ${drawerOpen ? "translate-x-0" : "translate-x-full"}`}>
                <div className="flex items-center gap-3 px-6 py-4 border-b border-slate-200 dark:border-slate-800 shrink-0">
                    <button onClick={closeDrawer} className="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-slate-500 dark:text-slate-400">
                        <span className="material-symbols-outlined">close</span>
                    </button>
                    <h2 className="text-base font-black text-slate-900 dark:text-white flex-1">Editar perfil</h2>
                </div>

                <div className="flex-1 overflow-y-auto px-6 py-6 space-y-6">
                    {saveError && (
                        <div className="flex items-center gap-2 px-4 py-3 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-600 dark:text-red-400 font-semibold">
                            <span className="material-symbols-outlined !text-base">error</span>
                            {saveError}
                        </div>
                    )}

                    <div className="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                        <div className="w-16 h-16 rounded-xl bg-primary/10 overflow-hidden flex items-center justify-center shrink-0">
                            {avatar
                                ? <img src={avatar} alt="" className="w-full h-full object-cover" onError={e => { (e.target as HTMLImageElement).style.display = "none"; }} />
                                : <span className="text-2xl font-black text-primary">{(name || user.email).charAt(0).toUpperCase()}</span>
                            }
                        </div>
                        <div className="flex-1 min-w-0">
                            <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">URL del avatar</label>
                            <input type="url" value={avatar} onChange={e => setAvatar(e.target.value)} className={inputClass} placeholder="https://..." />
                        </div>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Nombre</label>
                            <input type="text" value={name} onChange={e => setName(e.target.value)} className={inputClass} placeholder="Tu nombre" />
                        </div>
                        <div>
                            <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Slug</label>
                            <div className="relative">
                                <span className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-mono">@</span>
                                <input type="text" value={slug} onChange={e => setSlug(e.target.value.toLowerCase().replace(/[^a-z0-9\-_]/g, ""))} className={`${inputClass} pl-8`} placeholder="tu-slug" />
                            </div>
                        </div>
                    </div>

                    {isBusiness && (
                        <div>
                            <p className="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Ubicacion</p>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs text-slate-400 mb-1.5">Latitud</label>
                                    <input type="number" step="any" value={lat} onChange={e => setLat(e.target.value)} className={inputClass} placeholder="40.4168" />
                                </div>
                                <div>
                                    <label className="block text-xs text-slate-400 mb-1.5">Longitud</label>
                                    <input type="number" step="any" value={lng} onChange={e => setLng(e.target.value)} className={inputClass} placeholder="-3.7038" />
                                </div>
                            </div>
                        </div>
                    )}

                    {isAthlete && (
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Ciudad</label>
                                <input type="text" value={city} onChange={e => setCity(e.target.value)} className={inputClass} placeholder="Madrid" />
                            </div>
                            <div>
                                <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Fecha de nacimiento</label>
                                <input type="date" value={birthDate} onChange={e => setBirthDate(e.target.value)} className={inputClass} />
                            </div>
                        </div>
                    )}

                    {isGuide && (
                        <div className="space-y-4">
                            <div>
                                <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Ciudad</label>
                                <input type="text" value={city} onChange={e => setCity(e.target.value)} className={inputClass} placeholder="Madrid" />
                            </div>
                            <div>
                                <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Bio</label>
                                <textarea value={bio} onChange={e => setBio(e.target.value)} rows={3} className="w-full rounded-xl border border-slate-300 dark:border-slate-600 px-4 py-3 text-sm font-medium bg-white dark:bg-[#1a2333] focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all resize-none" placeholder="Cuentanos sobre ti..." />
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs text-slate-400 mb-1.5">Latitud</label>
                                    <input type="number" step="any" value={lat} onChange={e => setLat(e.target.value)} className={inputClass} placeholder="40.4168" />
                                </div>
                                <div>
                                    <label className="block text-xs text-slate-400 mb-1.5">Longitud</label>
                                    <input type="number" step="any" value={lng} onChange={e => setLng(e.target.value)} className={inputClass} placeholder="-3.7038" />
                                </div>
                            </div>
                            <div>
                                <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Deportes</label>
                                <div className="flex flex-wrap gap-2">
                                    {SPORT_OPTIONS.map(s => {
                                        const active = sports.includes(s.code);
                                        return (
                                            <button key={s.code} type="button" onClick={() => toggleSport(s.code)} className={`px-3 py-1.5 rounded-full text-xs font-bold border transition-all ${active ? "bg-primary text-white border-primary" : "bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-primary/50"}`}>{s.label}</button>
                                        );
                                    })}
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                <div className="border-t border-slate-200 dark:border-slate-800 px-6 py-4 flex justify-end gap-3 shrink-0">
                    <button onClick={closeDrawer} disabled={isSaving} className="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all disabled:opacity-50">
                        Cancelar
                    </button>
                    <button onClick={handleSave} disabled={isSaving} className="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-white text-sm font-bold hover:bg-primary/90 transition-all shadow-sm disabled:opacity-60">
                        {isSaving ? <span className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" /> : <span className="material-symbols-outlined !text-base">save</span>}
                        Guardar cambios
                    </button>
                </div>
            </div>
        </div>
    );
}
