import { useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { Header } from "../widgets/layout/Header";
import { Footer } from "../widgets/layout/Footer";
import { Loader } from "../shared/ui/Loader";
import { ErrorState } from "../shared/ui/ErrorState";
import { useAuthStore } from "../store/authStore";
import { getPublicProfile, followProfile, unfollowProfile } from "../features/user/api/profileApi";

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

const ROLE_BG: Record<string, string> = {
    ROLE_BUSINESS: "from-violet-600 to-purple-800",
    ROLE_ATHLETE: "from-blue-600 to-sky-800",
    ROLE_GUIDE: "from-emerald-600 to-teal-800",
};

const SPORT_LABEL: Record<string, string> = {
    hike: "Hiking", bike: "Cycling", run: "Running", ski: "Skiing",
    climb: "Climbing", kayak: "Kayaking", surf: "Surfing", swim: "Swimming",
};

export function PublicProfilePage() {
    const { slug } = useParams<{ slug: string }>();
    const navigate = useNavigate();
    const queryClient = useQueryClient();
    const { isAuthenticated, user, token } = useAuthStore();

    const isOwnProfile = isAuthenticated && user?.slug === slug;

    const { data: profile, isLoading, error } = useQuery({
        queryKey: ["profile", slug],
        queryFn: () => getPublicProfile(slug!, token),
        enabled: !!slug,
        staleTime: 30_000,
    });

    const [optimisticFollowing, setOptimisticFollowing] = useState<boolean | null>(null);
    const [optimisticCount, setOptimisticCount] = useState<number | null>(null);

    const follow = useMutation({
        mutationFn: () => followProfile(slug!, token!),
        onMutate: () => {
            setOptimisticFollowing(true);
            setOptimisticCount((profile?.followersCount ?? 0) + 1);
        },
        onError: () => { setOptimisticFollowing(null); setOptimisticCount(null); },
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ["profile", slug] }),
    });

    const unfollow = useMutation({
        mutationFn: () => unfollowProfile(slug!, token!),
        onMutate: () => {
            setOptimisticFollowing(false);
            setOptimisticCount(Math.max(0, (profile?.followersCount ?? 1) - 1));
        },
        onError: () => { setOptimisticFollowing(null); setOptimisticCount(null); },
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ["profile", slug] }),
    });

    if (isLoading) return <div className="h-screen flex items-center justify-center"><Loader /></div>;
    if (error || !profile) return <ErrorState message="Perfil no encontrado" />;

    const isFollowing = optimisticFollowing ?? profile.isFollowedByMe;
    const followersCount = optimisticCount ?? profile.followersCount;
    const gradientClass = ROLE_BG[profile.role] ?? "from-slate-600 to-slate-800";
    const isBusy = follow.isPending || unfollow.isPending;

    return (
        <div className="relative flex flex-col w-full overflow-x-hidden font-display bg-slate-50 dark:bg-background-dark text-slate-900 dark:text-white min-h-screen transition-colors duration-300">
            <Header />

            <main className="max-w-[900px] mx-auto w-full px-4 md:px-6 pb-20">
                {/* Breadcrumb */}
                <div className="flex flex-wrap items-center gap-2 py-6">
                    <button onClick={() => navigate(-1)} className="text-[#4c669a] text-sm font-medium hover:text-primary transition-colors flex items-center gap-1">
                        <span className="material-symbols-outlined text-sm">arrow_back</span>
                        Volver
                    </button>
                    <span className="material-symbols-outlined text-sm text-[#4c669a]">chevron_right</span>
                    <span className="text-slate-600 dark:text-slate-300 text-sm font-semibold">@{profile.slug}</span>
                </div>

                {/* Hero card */}
                <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm mb-6">
                    {/* Gradient banner */}
                    <div className={`h-28 bg-gradient-to-br ${gradientClass} relative`} />

                    {/* Avatar + identity */}
                    <div className="px-6 pb-6">
                        <div className="flex items-end justify-between -mt-10 mb-4">
                            <div className="w-20 h-20 rounded-2xl bg-white dark:bg-slate-800 ring-4 ring-white dark:ring-slate-900 shadow-xl overflow-hidden flex items-center justify-center shrink-0">
                                {profile.avatar
                                    ? <img src={profile.avatar} alt={profile.name} className="w-full h-full object-cover" />
                                    : <span className="text-3xl font-black text-primary">{profile.name.charAt(0).toUpperCase()}</span>
                                }
                            </div>
                            {/* Action buttons */}
                            <div className="flex items-center gap-2 mt-2">
                                {isOwnProfile ? (
                                    <button
                                        onClick={() => navigate("/me")}
                                        className="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-sm font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition-all"
                                    >
                                        <span className="material-symbols-outlined !text-base">edit</span>
                                        Editar perfil
                                    </button>
                                ) : isAuthenticated ? (
                                    <button
                                        onClick={() => isFollowing ? unfollow.mutate() : follow.mutate()}
                                        disabled={isBusy}
                                        className={`flex items-center gap-1.5 px-5 py-2 rounded-xl text-sm font-bold transition-all shadow-sm disabled:opacity-60 ${isFollowing
                                                ? "bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/30 dark:hover:text-red-400 border border-slate-200 dark:border-slate-700"
                                                : "bg-primary text-white hover:bg-blue-700 shadow-primary/20"
                                            }`}
                                    >
                                        {isBusy
                                            ? <span className="w-4 h-4 border-2 border-current/30 border-t-current rounded-full animate-spin" />
                                            : <span className="material-symbols-outlined !text-base">{isFollowing ? "person_remove" : "person_add"}</span>
                                        }
                                        {isFollowing ? "Siguiendo" : "Seguir"}
                                    </button>
                                ) : (
                                    <button
                                        onClick={() => navigate("/auth")}
                                        className="flex items-center gap-1.5 px-5 py-2 rounded-xl bg-primary text-white text-sm font-bold hover:bg-blue-700 transition-all shadow-sm"
                                    >
                                        <span className="material-symbols-outlined !text-base">person_add</span>
                                        Seguir
                                    </button>
                                )}
                            </div>
                        </div>

                        <div className="flex items-start gap-3 flex-wrap">
                            <div className="flex-1 min-w-0">
                                <h1 className="text-2xl font-black text-slate-900 dark:text-white">{profile.name}</h1>
                                <p className="text-sm text-slate-500 dark:text-slate-400 font-mono mt-0.5">@{profile.slug}</p>
                            </div>
                            <span className={`text-xs font-black px-3 py-1.5 rounded-full mt-1 ${ROLE_COLOR[profile.role] ?? "bg-slate-100 text-slate-600"}`}>
                                {ROLE_LABEL[profile.role] ?? profile.role}
                            </span>
                        </div>

                        {/* Stats strip */}
                        <div className="flex items-center gap-6 mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                            <div className="text-center">
                                <p className="text-xl font-black text-slate-900 dark:text-white">{followersCount}</p>
                                <p className="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Seguidores</p>
                            </div>
                            <div className="w-px h-8 bg-slate-200 dark:bg-slate-700" />
                            <div className="text-center">
                                <p className="text-xl font-black text-slate-900 dark:text-white">{profile.followingCount}</p>
                                <p className="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Siguiendo</p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Role-specific info */}
                {profile.role === "ROLE_GUIDE" && (
                    <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-6 space-y-4">
                        <h2 className="text-sm font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Sobre el guía</h2>
                        {profile.bio && (
                            <p className="text-slate-700 dark:text-slate-300 leading-relaxed">{profile.bio}</p>
                        )}
                        <div className="flex flex-wrap gap-4 text-sm">
                            {profile.city && (
                                <div className="flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
                                    <span className="material-symbols-outlined !text-base text-primary">location_on</span>
                                    {profile.city}
                                </div>
                            )}
                        </div>
                        {profile.sports && profile.sports.length > 0 && (
                            <div>
                                <p className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Deportes</p>
                                <div className="flex flex-wrap gap-2">
                                    {profile.sports.map(s => (
                                        <span key={s} className="px-3 py-1.5 rounded-full bg-primary/10 text-primary text-xs font-bold">
                                            {SPORT_LABEL[s] ?? s}
                                        </span>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                )}

                {profile.role === "ROLE_ATHLETE" && (profile.city || profile.birthDate) && (
                    <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
                        <h2 className="text-sm font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">Sobre el atleta</h2>
                        <div className="flex flex-wrap gap-6">
                            {profile.city && (
                                <div className="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                                    <span className="material-symbols-outlined !text-base text-primary">location_on</span>
                                    {profile.city}
                                </div>
                            )}
                            {profile.birthDate && (
                                <div className="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                                    <span className="material-symbols-outlined !text-base text-primary">cake</span>
                                    {new Date(profile.birthDate).toLocaleDateString("es-ES", { day: "numeric", month: "long", year: "numeric" })}
                                </div>
                            )}
                        </div>
                    </div>
                )}

                {profile.role === "ROLE_BUSINESS" && (
                    <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
                        <h2 className="text-sm font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Establecimiento</h2>
                        <p className="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                            Este establecimiento colabora con VAMO para ofrecer las mejores experiencias y productos a la comunidad de deportistas.
                        </p>
                    </div>
                )}
            </main>

            <Footer />
        </div>
    );
}
