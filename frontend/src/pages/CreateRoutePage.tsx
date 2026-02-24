import { useState, useEffect, useRef, useCallback } from "react";
import { useNavigate } from "react-router-dom";
import { useQuery, useMutation } from "@tanstack/react-query";
import { useAuthStore } from "../store/authStore";
import { createRoute } from "../features/routes/api/routeApi";
import { http } from "../shared/api/http";
import { endpoints } from "../shared/api/endpoints";
import { HttpError } from "../shared/api/http";

// Icon + Spanish label per sport code (DB codes are uppercase; comparison is case-insensitive)
const SPORT_META: Record<string, { label: string; icon: string }> = {
    HIKE: { label: "Senderismo", icon: "hiking" },
    BIKE: { label: "Ciclismo", icon: "directions_bike" },
    RUN: { label: "Running", icon: "directions_run" },
    SKI: { label: "Esquí", icon: "downhill_skiing" },
    CLIMB: { label: "Escalada", icon: "landscape" },
    KAYAK: { label: "Kayak", icon: "kayaking" },
    SURF: { label: "Surf", icon: "surfing" },
    SWIM: { label: "Natación", icon: "pool" },
};

interface SportOption { code: string; name: string; }

function slugify(str: string): string {
    return str
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/^-+|-+$/g, "")
        .slice(0, 60);
}

function formatSize(bytes: number): string {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

export function CreateRoutePage() {
    const navigate = useNavigate();
    const { isAuthenticated, user, token } = useAuthStore();

    useEffect(() => {
        if (!isAuthenticated) { navigate("/auth", { replace: true }); return; }
        const role = user?.role ?? "";
        if (role !== "ROLE_ATHLETE" && role !== "ROLE_GUIDE" && role !== "ROLE_ADMIN") {
            navigate("/routes", { replace: true });
        }
    }, [isAuthenticated, user, navigate]);

    // Fetch sports from API
    const { data: sportsData = [] } = useQuery<SportOption[]>({
        queryKey: ["sports"],
        queryFn: () => http<SportOption[]>("GET", endpoints.sports.list),
        staleTime: Infinity,
    });

    // Default to first sport once loaded
    const [sportCode, setSportCode] = useState("");
    useEffect(() => {
        if (sportsData.length > 0 && sportCode === "") {
            setSportCode(sportsData[0].code);
        }
    }, [sportsData, sportCode]);

    // File state
    const [gpxFile, setGpxFile] = useState<File | null>(null);
    const [dragging, setDragging] = useState(false);
    const fileInputRef = useRef<HTMLInputElement>(null);

    // Form state
    const [title, setTitle] = useState("");
    const [slug, setSlug] = useState("");
    const [slugTouched, setSlugTouched] = useState(false);
    const [description, setDescription] = useState("");
    const [visibility, setVisibility] = useState<"PUBLIC" | "UNLISTED" | "PRIVATE">("PUBLIC");
    const [difficulty, setDifficulty] = useState<"EASY" | "MODERATE" | "HARD" | "EXPERT">("MODERATE");
    const [routeType, setRouteType] = useState<"CIRCULAR" | "LINEAR" | "ROUND_TRIP" | "">("CIRCULAR");

    // Auto-slug from title unless manually edited
    useEffect(() => {
        if (!slugTouched) setSlug(slugify(title));
    }, [title, slugTouched]);

    const handleFileChange = useCallback((file: File | null) => {
        if (!file) return;
        if (!file.name.toLowerCase().endsWith(".gpx") && file.type !== "application/gpx+xml") {
            alert("Solo se aceptan archivos GPX.");
            return;
        }
        setGpxFile(file);
    }, []);

    const onDrop = useCallback((e: React.DragEvent) => {
        e.preventDefault();
        setDragging(false);
        const f = e.dataTransfer.files[0];
        if (f) handleFileChange(f);
    }, [handleFileChange]);

    const mutation = useMutation({
        mutationFn: () => {
            if (!token || !gpxFile) throw new Error("missing");
            return createRoute(token, {
                title: title.trim(),
                slug: slug.trim(),
                description: description.trim() || undefined,
                sportCode,
                visibility,
                status: "PUBLISHED",
                difficulty,
                routeType: routeType || undefined,
                file: gpxFile,
            });
        },
        onSuccess: () => {
            navigate(`/route/${slug.trim()}`);
        },
    });

    const canSubmit = !!gpxFile && title.trim() !== "" && sportCode !== "" && !mutation.isPending;

    const errorMsg = (() => {
        if (!mutation.error) return null;
        if (mutation.error instanceof HttpError) {
            const body = mutation.error.body as Record<string, unknown>;
            const msg = (body.message ?? body.error ?? "") as string;
            const map: Record<string, string> = {
                slug_already_taken: "Ese slug ya está en uso. Elige otro.",
                sport_not_found: "Deporte no encontrado.",
                unsupported_format: "Formato de archivo no soportado.",
                cannot_follow_yourself: "",
                "Unsupported route format.": "Formato de archivo no soportado.",
                "Invalid GPX file.": "El archivo GPX no es válido o está corrupto.",
            };
            return map[msg] ?? msg ?? "Error al crear la ruta. Inténtalo de nuevo.";
        }
        return "Error inesperado. Inténtalo de nuevo.";
    })();

    const inputClass = "w-full rounded-xl border border-slate-300 dark:border-slate-600 h-12 px-4 text-sm font-medium bg-white dark:bg-[#1a2333] focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all";

    return (
        <div className="min-h-screen bg-slate-50 dark:bg-background-dark">
            {/* Header */}
            <div className="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 sticky top-0 z-10">
                <div className="max-w-2xl mx-auto px-4 sm:px-6 py-4 flex items-center gap-3">
                    <button onClick={() => navigate(-1)} className="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-slate-500 dark:text-slate-400">
                        <span className="material-symbols-outlined">arrow_back</span>
                    </button>
                    <h1 className="text-lg font-black text-slate-900 dark:text-white flex-1">Subir ruta</h1>
                </div>
            </div>

            <div className="max-w-2xl mx-auto px-4 sm:px-6 py-8 space-y-6">
                {/* Error banner */}
                {errorMsg && (
                    <div className="flex items-start gap-2 px-4 py-3 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-600 dark:text-red-400 font-semibold">
                        <span className="material-symbols-outlined !text-base mt-0.5 shrink-0">error</span>
                        {errorMsg}
                    </div>
                )}

                {/* GPX Upload */}
                <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <h2 className="text-sm font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">
                        Archivo GPX
                    </h2>

                    {gpxFile ? (
                        <div className="flex items-center gap-4 p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800 rounded-xl">
                            <div className="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center shrink-0">
                                <span className="material-symbols-outlined text-emerald-600 dark:text-emerald-400">route</span>
                            </div>
                            <div className="flex-1 min-w-0">
                                <p className="text-sm font-bold text-slate-900 dark:text-white truncate">{gpxFile.name}</p>
                                <p className="text-xs text-slate-500 dark:text-slate-400">{formatSize(gpxFile.size)}</p>
                            </div>
                            <button
                                onClick={() => { setGpxFile(null); if (fileInputRef.current) fileInputRef.current.value = ""; }}
                                className="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 transition-colors"
                            >
                                <span className="material-symbols-outlined !text-base">close</span>
                            </button>
                        </div>
                    ) : (
                        <div
                            onClick={() => fileInputRef.current?.click()}
                            onDragOver={e => { e.preventDefault(); setDragging(true); }}
                            onDragLeave={() => setDragging(false)}
                            onDrop={onDrop}
                            className={`cursor-pointer rounded-xl border-2 border-dashed transition-all p-10 flex flex-col items-center gap-3 ${dragging
                                ? "border-primary bg-primary/5 dark:bg-primary/10"
                                : "border-slate-300 dark:border-slate-600 hover:border-primary hover:bg-slate-50 dark:hover:bg-slate-800/50"
                                }`}
                        >
                            <div className="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center">
                                <span className="material-symbols-outlined !text-3xl text-primary">upload_file</span>
                            </div>
                            <div className="text-center">
                                <p className="text-sm font-bold text-slate-700 dark:text-slate-200">
                                    Arrastra tu archivo GPX aquí
                                </p>
                                <p className="text-xs text-slate-400 dark:text-slate-500 mt-1">o haz clic para seleccionarlo</p>
                            </div>
                            <span className="text-[11px] px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold">
                                Solo archivos .gpx
                            </span>
                        </div>
                    )}

                    <input
                        ref={fileInputRef}
                        type="file"
                        accept=".gpx,application/gpx+xml"
                        className="hidden"
                        onChange={e => handleFileChange(e.target.files?.[0] ?? null)}
                    />
                </div>

                {/* Metadata */}
                <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 space-y-5">
                    <h2 className="text-sm font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        Detalles de la ruta
                    </h2>

                    {/* Title */}
                    <div>
                        <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                            Título <span className="text-red-400">*</span>
                        </label>
                        <input
                            type="text"
                            value={title}
                            onChange={e => setTitle(e.target.value)}
                            placeholder="Mi ruta por la sierra"
                            className={inputClass}
                            maxLength={120}
                        />
                    </div>

                    {/* Description */}
                    <div>
                        <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                            Descripción
                        </label>
                        <textarea
                            value={description}
                            onChange={e => setDescription(e.target.value)}
                            rows={3}
                            placeholder="Describe la ruta, puntos de interés, dificultad..."
                            className="w-full rounded-xl border border-slate-300 dark:border-slate-600 px-4 py-3 text-sm font-medium bg-white dark:bg-[#1a2333] focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all resize-none"
                            maxLength={2000}
                        />
                    </div>

                    {/* Sport */}
                    <div>
                        <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">
                            Deporte <span className="text-red-400">*</span>
                        </label>
                        {sportsData.length === 0 ? (
                            <div className="flex justify-center py-6">
                                <span className="w-5 h-5 border-2 border-primary/30 border-t-primary rounded-full animate-spin" />
                            </div>
                        ) : (
                            <div className="grid grid-cols-4 sm:grid-cols-4 gap-2">
                                {sportsData.map(s => {
                                    const meta = SPORT_META[s.code.toUpperCase()];
                                    const active = sportCode === s.code;
                                    return (
                                        <button
                                            key={s.code}
                                            type="button"
                                            onClick={() => setSportCode(s.code)}
                                            className={`flex flex-col items-center gap-1 py-3 px-2 rounded-xl border transition-all text-center ${active
                                                ? "bg-primary text-white border-primary shadow-sm"
                                                : "border-slate-200 dark:border-slate-700 hover:border-primary/50 text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800/50"
                                                }`}
                                        >
                                            {meta ? (
                                                <span className={`material-symbols-outlined !text-xl ${active ? "text-white" : "text-primary"}`}>
                                                    {meta.icon}
                                                </span>
                                            ) : (
                                                <span className={`material-symbols-outlined !text-xl ${active ? "text-white" : "text-primary"}`}>sports</span>
                                            )}
                                            <span className="text-[10px] font-black leading-tight">
                                                {meta?.label ?? s.name}
                                            </span>
                                        </button>
                                    );
                                })}
                            </div>
                        )}
                    </div>

                    {/* Difficulty */}
                    <div>
                        <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">
                            Dificultad
                        </label>
                        <div className="flex gap-2 flex-wrap">
                            {([
                                { value: "EASY", label: "Fácil", icon: "sentiment_satisfied" },
                                { value: "MODERATE", label: "Moderada", icon: "sentiment_neutral" },
                                { value: "HARD", label: "Difícil", icon: "sentiment_dissatisfied" },
                                { value: "EXPERT", label: "Experto", icon: "local_fire_department" },
                            ] as const).map(opt => {
                                const activeClass =
                                    opt.value === "EASY" ? "bg-emerald-50 dark:bg-emerald-950/30 border-emerald-500 text-emerald-600 dark:text-emerald-400"
                                        : opt.value === "MODERATE" ? "bg-amber-50 dark:bg-amber-950/30 border-amber-500 text-amber-600 dark:text-amber-400"
                                            : opt.value === "HARD" ? "bg-orange-50 dark:bg-orange-950/30 border-orange-500 text-orange-600 dark:text-orange-400"
                                                : "bg-red-50 dark:bg-red-950/30 border-red-600 text-red-600 dark:text-red-400";
                                return (
                                    <button
                                        key={opt.value}
                                        type="button"
                                        onClick={() => setDifficulty(opt.value)}
                                        className={`flex-1 flex flex-col items-center gap-1 py-3 px-3 rounded-xl border transition-all min-w-[75px] ${difficulty === opt.value
                                                ? activeClass
                                                : "border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800/50"
                                            }`}
                                    >
                                        <span className="material-symbols-outlined !text-base">{opt.icon}</span>
                                        <span className="text-xs font-black">{opt.label}</span>
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    {/* Tipo de ruta */}
                    <div>
                        <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">
                            Tipo de ruta
                        </label>
                        <div className="flex gap-2 flex-wrap">
                            {([
                                { value: "CIRCULAR", label: "Circular", icon: "loop" },
                                { value: "LINEAR", label: "Lineal", icon: "trending_flat" },
                                { value: "ROUND_TRIP", label: "Ida y vuelta", icon: "sync_alt" },
                            ] as const).map(opt => {
                                const activeClass =
                                    opt.value === "CIRCULAR" ? "bg-blue-50 dark:bg-blue-950/30 border-blue-500 text-blue-600 dark:text-blue-400"
                                        : opt.value === "LINEAR" ? "bg-amber-50 dark:bg-amber-950/30 border-amber-500 text-amber-600 dark:text-amber-400"
                                            : "bg-teal-50 dark:bg-teal-950/30 border-teal-500 text-teal-600 dark:text-teal-400";
                                return (
                                    <button
                                        key={opt.value}
                                        type="button"
                                        onClick={() => setRouteType(routeType === opt.value ? "" : opt.value)}
                                        className={`flex-1 flex flex-col items-center gap-1 py-3 px-3 rounded-xl border transition-all min-w-[90px] ${routeType === opt.value
                                                ? activeClass
                                                : "border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800/50"
                                            }`}
                                    >
                                        <span className="material-symbols-outlined !text-base">{opt.icon}</span>
                                        <span className="text-xs font-black">{opt.label}</span>
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    {/* Visibility */}
                    <div>
                        <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">
                            Visibilidad
                        </label>
                        <div className="flex gap-2 flex-wrap">
                            {([
                                { value: "PUBLIC", label: "Pública", icon: "public", desc: "Visible para todos" },
                                { value: "UNLISTED", label: "No listada", icon: "link", desc: "Solo con enlace" },
                                { value: "PRIVATE", label: "Privada", icon: "lock", desc: "Solo tú" },
                            ] as const).map(opt => (
                                <button
                                    key={opt.value}
                                    type="button"
                                    onClick={() => setVisibility(opt.value)}
                                    className={`flex-1 flex flex-col items-center gap-1 py-3 px-3 rounded-xl border transition-all min-w-[90px] ${visibility === opt.value
                                        ? "bg-primary/10 dark:bg-primary/20 border-primary text-primary"
                                        : "border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800/50"
                                        }`}
                                >
                                    <span className="material-symbols-outlined !text-base">{opt.icon}</span>
                                    <span className="text-xs font-black">{opt.label}</span>
                                    <span className="text-[9px] text-slate-400 dark:text-slate-500">{opt.desc}</span>
                                </button>
                            ))}
                        </div>
                    </div>
                </div>

                {/* Submit */}
                <div className="flex gap-3">
                    <button
                        onClick={() => navigate(-1)}
                        disabled={mutation.isPending}
                        className="flex-1 py-3.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all disabled:opacity-50"
                    >
                        Cancelar
                    </button>
                    <button
                        onClick={() => mutation.mutate()}
                        disabled={!canSubmit}
                        className="flex-[2] flex items-center justify-center gap-2 py-3.5 rounded-xl bg-primary text-white text-sm font-black hover:bg-primary/90 transition-all shadow-sm shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {mutation.isPending ? (
                            <>
                                <span className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                                Subiendo...
                            </>
                        ) : (
                            <>
                                <span className="material-symbols-outlined !text-base">upload</span>
                                Publicar ruta
                            </>
                        )}
                    </button>
                </div>

                <p className="text-xs text-slate-400 dark:text-slate-500 text-center pb-8">
                    La ruta se analizará automáticamente a partir del archivo GPX. La distancia, el desnivel y el recorrido se calcularán de forma automática.
                </p>
            </div>
        </div>
    );
}
