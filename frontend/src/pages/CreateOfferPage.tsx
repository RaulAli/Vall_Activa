import { useState } from "react";
import { useNavigate, Link } from "react-router-dom";
import { useMutation } from "@tanstack/react-query";
import { useAuthStore } from "../store/authStore";
import { createOffer } from "../features/offers/api/offerApi";
import { Header } from "../widgets/layout/Header";
import { HttpError } from "../shared/api/http";

type DiscountType = "NONE" | "AMOUNT" | "PERCENT";
type PaymentType = "MONEY" | "POINTS";

function slugify(str: string): string {
    return str
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/^-|-$/g, "");
}

const DISCOUNT_OPTIONS: { value: DiscountType; label: string; icon: string }[] = [
    { value: "NONE", label: "Sin descuento", icon: "sell" },
    { value: "AMOUNT", label: "Importe fijo", icon: "euro" },
    { value: "PERCENT", label: "Porcentaje", icon: "percent" },
];

export function CreateOfferPage() {
    const navigate = useNavigate();
    const { token, user } = useAuthStore();

    const [title, setTitle] = useState("");
    const [slug, setSlug] = useState("");
    const [slugManual, setSlugManual] = useState(false);
    const [description, setDescription] = useState("");
    const [paymentType, setPaymentType] = useState<PaymentType>("MONEY");
    const [price, setPrice] = useState("0.00");
    const [currency] = useState("EUR");
    const [pointsCost, setPointsCost] = useState(0);
    const [quantity, setQuantity] = useState(0);
    const [image, setImage] = useState("");
    const [discountType, setDiscountType] = useState<DiscountType>("NONE");
    const [status, setStatus] = useState<"DRAFT" | "PUBLISHED">("DRAFT");
    const [error, setError] = useState<string | null>(null);

    const handleTitleChange = (v: string) => {
        setTitle(v);
        if (!slugManual) setSlug(slugify(v));
    };

    const mutation = useMutation({
        mutationFn: () => createOffer(token!, {
            title: title.trim(),
            slug: slug.trim(),
            description: description.trim() || undefined,
            price: paymentType === "MONEY" ? price : "0.00",
            currency,
            isActive: true,
            quantity,
            pointsCost: paymentType === "POINTS" ? pointsCost : 0,
            image: image.trim() || undefined,
            discountType: paymentType === "POINTS" ? "NONE" : discountType,
            status,
        }),
        onSuccess: (id) => navigate(`/offer/${slug.trim()}`),
        onError: (err) => {
            if (err instanceof HttpError) {
                const msg = (err.body as Record<string, string>).message ?? "Error al crear la oferta";
                setError(msg);
            } else {
                setError("Error inesperado");
            }
        },
    });

    const canSubmit =
        title.trim() !== "" &&
        slug.trim() !== "" &&
        (paymentType === "MONEY" ? /^\d+(\.\d{1,2})?$/.test(price) && parseFloat(price) > 0 : pointsCost > 0);

    if (!token || user?.role !== "ROLE_BUSINESS") {
        return (
            <>
                <Header />
                <div className="min-h-screen flex items-center justify-center text-slate-500">
                    Solo los negocios pueden crear ofertas.
                </div>
            </>
        );
    }

    const hasLocation = user.lat != null && user.lng != null;

    if (!hasLocation) {
        return (
            <>
                <Header />
                <div className="max-w-lg mx-auto px-4 py-20 flex flex-col items-center text-center gap-6">
                    <div className="w-16 h-16 rounded-2xl bg-amber-100 dark:bg-amber-950/30 flex items-center justify-center">
                        <span className="material-symbols-outlined text-amber-500 !text-3xl">location_on</span>
                    </div>
                    <div>
                        <h2 className="text-xl font-black text-slate-900 dark:text-white mb-2">Ubica tu negocio primero</h2>
                        <p className="text-sm text-slate-500 dark:text-slate-400">
                            Para crear ofertas necesitas configurar la ubicación de tu negocio.
                            Así tus clientes podrán encontrarte en el mapa.
                        </p>
                    </div>
                    <Link
                        to="/me"
                        className="flex items-center gap-2 px-6 py-3 bg-primary hover:bg-blue-600 text-white font-black text-sm rounded-xl shadow-lg shadow-primary/20 transition-all"
                    >
                        <span className="material-symbols-outlined !text-base">edit_location</span>
                        Ir a Mi Perfil
                    </Link>
                    <button onClick={() => navigate(-1)} className="text-sm text-slate-400 hover:text-slate-600 transition-colors">
                        Volver
                    </button>
                </div>
            </>
        );
    }

    return (
        <>
            <Header />
            <div className="max-w-2xl mx-auto px-4 py-8">
                {/* Page header */}
                <div className="flex items-center gap-3 mb-8">
                    <button
                        onClick={() => navigate(-1)}
                        className="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-white transition-colors"
                    >
                        <span className="material-symbols-outlined">arrow_back</span>
                    </button>
                    <div>
                        <h1 className="text-2xl font-black text-slate-900 dark:text-white">Nueva oferta</h1>
                        <p className="text-sm text-slate-500 mt-0.5">Crea una oferta para tus clientes</p>
                    </div>
                </div>

                <div className="flex flex-col gap-5">
                    {/* Title */}
                    <div>
                        <label className="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Título *</label>
                        <input
                            type="text"
                            value={title}
                            onChange={e => handleTitleChange(e.target.value)}
                            placeholder="Ej: Pack de escalada para principiantes"
                            className="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                        />
                    </div>

                    {/* Slug */}
                    <div>
                        <label className="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">
                            URL slug *
                        </label>
                        <div className="flex items-center gap-2">
                            <span className="text-sm text-slate-400 shrink-0">/offer/</span>
                            <input
                                type="text"
                                value={slug}
                                onChange={e => { setSlug(e.target.value); setSlugManual(true); }}
                                className="flex-1 px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                            />
                        </div>
                    </div>

                    {/* Description */}
                    <div>
                        <label className="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Descripción</label>
                        <textarea
                            value={description}
                            onChange={e => setDescription(e.target.value)}
                            rows={3}
                            placeholder="Describe lo que incluye esta oferta..."
                            className="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all resize-none"
                        />
                    </div>

                    {/* Payment type */}
                    <div>
                        <label className="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Tipo de pago *</label>
                        <div className="grid grid-cols-2 gap-2">
                            {(["MONEY", "POINTS"] as PaymentType[]).map(pt => (
                                <button
                                    key={pt}
                                    onClick={() => setPaymentType(pt)}
                                    className={`flex items-center gap-2 px-4 py-3 rounded-xl border text-sm font-bold transition-all ${paymentType === pt
                                        ? "bg-primary/5 border-primary/40 text-primary dark:bg-primary/10"
                                        : "bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-500 hover:border-slate-300"
                                        }`}
                                >
                                    <span className="material-symbols-outlined !text-lg">{pt === "MONEY" ? "euro" : "stars"}</span>
                                    {pt === "MONEY" ? "Dinero (€)" : "Puntos"}
                                </button>
                            ))}
                        </div>
                        <p className="text-[11px] text-slate-400 mt-1.5">
                            {paymentType === "MONEY"
                                ? "El cliente paga con dinero real."
                                : "El cliente canjea puntos acumulados de la app."}
                        </p>
                    </div>

                    {/* Price / Points + Quantity */}
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            {paymentType === "MONEY" ? (
                                <>
                                    <label className="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Precio ({currency}) *</label>
                                    <div className="relative">
                                        <span className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">€</span>
                                        <input
                                            type="text"
                                            value={price}
                                            onChange={e => setPrice(e.target.value)}
                                            placeholder="0.00"
                                            className="w-full pl-8 pr-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                                        />
                                    </div>
                                </>
                            ) : (
                                <>
                                    <label className="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Coste en puntos *</label>
                                    <div className="relative">
                                        <span className="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-amber-400 !text-base">stars</span>
                                        <input
                                            type="number"
                                            min={1}
                                            value={pointsCost}
                                            onChange={e => setPointsCost(Math.max(1, parseInt(e.target.value) || 1))}
                                            className="w-full pl-9 pr-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                                        />
                                    </div>
                                </>
                            )}
                        </div>
                        <div>
                            <label className="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Stock (0 = ilimitado)</label>
                            <input
                                type="number"
                                min={0}
                                value={quantity}
                                onChange={e => setQuantity(Math.max(0, parseInt(e.target.value) || 0))}
                                className="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                            />
                        </div>
                    </div>

                    {/* Discount type (only for MONEY) */}
                    {paymentType === "MONEY" && (
                        <div>
                            <label className="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Tipo de descuento</label>
                            <div className="grid grid-cols-3 gap-2">
                                {DISCOUNT_OPTIONS.map(opt => (
                                    <button
                                        key={opt.value}
                                        onClick={() => setDiscountType(opt.value)}
                                        className={`flex flex-col items-center gap-1.5 px-3 py-3 rounded-xl border text-xs font-bold transition-all ${discountType === opt.value
                                            ? "bg-primary/5 border-primary/40 text-primary dark:bg-primary/10"
                                            : "bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-500 hover:border-slate-300"
                                            }`}
                                    >
                                        <span className="material-symbols-outlined !text-lg">{opt.icon}</span>
                                        {opt.label}
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Image URL */}
                    <div>
                        <label className="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">URL de imagen</label>
                        <input
                            type="url"
                            value={image}
                            onChange={e => setImage(e.target.value)}
                            placeholder="https://..."
                            className="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                        />
                    </div>

                    {/* Status */}
                    <div>
                        <label className="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Estado al crear</label>
                        <div className="flex gap-2">
                            {(["PUBLISHED", "DRAFT"] as const).map(s => (
                                <button
                                    key={s}
                                    onClick={() => setStatus(s)}
                                    className={`flex items-center gap-2 px-4 py-2.5 rounded-xl border text-sm font-bold transition-all ${status === s
                                        ? s === "PUBLISHED"
                                            ? "bg-blue-50 dark:bg-blue-950/30 border-blue-300 dark:border-blue-700 text-blue-600 dark:text-blue-400"
                                            : "bg-slate-100 dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300"
                                        : "bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-400 hover:border-slate-300"
                                        }`}
                                >
                                    <span className={`w-2 h-2 rounded-full ${s === "PUBLISHED" ? "bg-blue-500" : "bg-slate-400"}`} />
                                    {s === "PUBLISHED" ? "Publicada" : "Borrador"}
                                </button>
                            ))}
                        </div>
                    </div>

                    {/* Error */}
                    {error && (
                        <div className="flex items-center gap-2 px-4 py-3 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-600 dark:text-red-400">
                            <span className="material-symbols-outlined !text-base">error</span>
                            {error}
                        </div>
                    )}

                    {/* Submit */}
                    <button
                        disabled={!canSubmit || mutation.isPending}
                        onClick={() => { setError(null); mutation.mutate(); }}
                        className="w-full py-4 bg-primary hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black text-sm rounded-xl shadow-lg shadow-primary/20 transition-all active:scale-[0.98] flex items-center justify-center gap-2"
                    >
                        {mutation.isPending ? (
                            <span className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                        ) : (
                            <span className="material-symbols-outlined !text-base">add_circle</span>
                        )}
                        {mutation.isPending ? "Creando..." : "Crear oferta"}
                    </button>
                </div>
            </div>
        </>
    );
}
