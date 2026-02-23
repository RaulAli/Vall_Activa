import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { useAuthStore } from "../store/authStore";
import { getMyOffers, updateOffer } from "../features/offers/api/offerApi";
import { getFallbackImage } from "../shared/utils/images";
import { Header } from "../widgets/layout/Header";
import type { MyOfferItem } from "../features/offers/domain/types";

const STATUS_CONFIG = {
    PUBLISHED: { label: "Publicada", color: "text-blue-600 dark:text-blue-400", dot: "bg-blue-500", bg: "bg-blue-50 dark:bg-blue-950/30 border-blue-200 dark:border-blue-800" },
    DRAFT: { label: "Borrador", color: "text-slate-400", dot: "bg-slate-400", bg: "bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700" },
    ARCHIVED: { label: "Archivada", color: "text-slate-400", dot: "bg-slate-300", bg: "bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700" },
} as const;

type StatusFilter = "ALL" | "PUBLISHED" | "DRAFT" | "ARCHIVED";

export function MyOffersPage() {
    const navigate = useNavigate();
    const { token } = useAuthStore();
    const queryClient = useQueryClient();

    const [statusFilter, setStatusFilter] = useState<StatusFilter>("ALL");
    const [editingId, setEditingId] = useState<string | null>(null);

    const { data: offers = [], isLoading, isError } = useQuery({
        queryKey: ["me", "offers"],
        queryFn: () => getMyOffers(token!),
        enabled: !!token,
    });

    const updateMut = useMutation({
        mutationFn: ({ id, patch }: { id: string; patch: Parameters<typeof updateOffer>[2] }) =>
            updateOffer(token!, id, patch),
        onMutate: async ({ id, patch }) => {
            await queryClient.cancelQueries({ queryKey: ["me", "offers"] });
            const prev = queryClient.getQueryData<MyOfferItem[]>(["me", "offers"]);
            queryClient.setQueryData<MyOfferItem[]>(["me", "offers"], (old = []) =>
                old.map(o => o.id === id ? { ...o, ...patch } : o)
            );
            return { prev };
        },
        onError: (_e, _v, ctx) => {
            if (ctx?.prev) queryClient.setQueryData(["me", "offers"], ctx.prev);
        },
        onSettled: () => queryClient.invalidateQueries({ queryKey: ["me", "offers"] }),
    });

    const filtered = statusFilter === "ALL" ? offers : offers.filter(o => o.status === statusFilter);

    const stats = {
        total: offers.length,
        published: offers.filter(o => o.status === "PUBLISHED").length,
        draft: offers.filter(o => o.status === "DRAFT").length,
        archived: offers.filter(o => o.status === "ARCHIVED").length,
    };

    if (!token) {
        return (
            <>
                <Header />
                <div className="min-h-screen flex items-center justify-center text-slate-500">Debes iniciar sesión.</div>
            </>
        );
    }

    return (
        <>
            <Header />
            <div className="max-w-4xl mx-auto px-4 py-8">
                {/* Page header */}
                <div className="flex items-center gap-3 mb-6">
                    <button
                        onClick={() => navigate(-1)}
                        className="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-white transition-colors"
                    >
                        <span className="material-symbols-outlined">arrow_back</span>
                    </button>
                    <div className="flex-1">
                        <h1 className="text-2xl font-black text-slate-900 dark:text-white">Mis ofertas</h1>
                        <p className="text-sm text-slate-500 mt-0.5">{stats.total} oferta{stats.total !== 1 ? "s" : ""} en total</p>
                    </div>
                    <button
                        onClick={() => navigate("/offers/new")}
                        className="flex items-center gap-2 px-4 py-2.5 bg-primary hover:bg-blue-600 text-white text-sm font-bold rounded-xl shadow-lg shadow-primary/20 transition-all active:scale-95"
                    >
                        <span className="material-symbols-outlined !text-base">add</span>
                        Nueva oferta
                    </button>
                </div>

                {/* Stats */}
                <div className="grid grid-cols-3 gap-3 mb-6">
                    {([
                        { key: "PUBLISHED", label: "Publicadas", count: stats.published, icon: "check_circle", color: "text-blue-500" },
                        { key: "DRAFT", label: "Borradores", count: stats.draft, icon: "edit_note", color: "text-slate-400" },
                        { key: "ARCHIVED", label: "Archivadas", count: stats.archived, icon: "inventory_2", color: "text-slate-300" },
                    ] as const).map(s => (
                        <button
                            key={s.key}
                            onClick={() => setStatusFilter(v => v === s.key ? "ALL" : s.key)}
                            className={`flex flex-col items-center gap-1 p-3 rounded-xl border transition-all ${statusFilter === s.key
                                    ? "bg-primary/5 border-primary/30 dark:bg-primary/10"
                                    : "bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 hover:border-slate-300"
                                }`}
                        >
                            <span className={`material-symbols-outlined !text-lg ${s.color}`}>{s.icon}</span>
                            <span className="text-xl font-black text-slate-900 dark:text-white">{s.count}</span>
                            <span className="text-[10px] font-bold uppercase tracking-widest text-slate-400">{s.label}</span>
                        </button>
                    ))}
                </div>

                {/* Filter chips */}
                <div className="flex gap-2 mb-4 flex-wrap">
                    {(["ALL", "PUBLISHED", "DRAFT", "ARCHIVED"] as StatusFilter[]).map(f => (
                        <button
                            key={f}
                            onClick={() => setStatusFilter(f)}
                            className={`px-3 py-1.5 rounded-full text-xs font-bold border transition-all ${statusFilter === f
                                    ? "bg-primary text-white border-primary shadow-sm"
                                    : "bg-white dark:bg-slate-900 text-slate-500 border-slate-200 dark:border-slate-700 hover:border-slate-300"
                                }`}
                        >
                            {f === "ALL" ? "Todas" : STATUS_CONFIG[f].label}
                        </button>
                    ))}
                </div>

                {/* Content */}
                {isLoading && (
                    <div className="flex flex-col gap-3">
                        {[1, 2, 3].map(i => <div key={i} className="h-24 bg-slate-100 dark:bg-slate-800 rounded-xl animate-pulse" />)}
                    </div>
                )}

                {isError && (
                    <div className="text-center py-12 text-red-500">
                        <span className="material-symbols-outlined !text-4xl mb-2 block">error</span>
                        <p className="font-semibold">Error al cargar las ofertas</p>
                    </div>
                )}

                {!isLoading && !isError && filtered.length === 0 && (
                    <div className="text-center py-16">
                        <span className="material-symbols-outlined !text-5xl text-slate-300 dark:text-slate-600 mb-3 block">sell</span>
                        <p className="font-bold text-slate-500 dark:text-slate-400">
                            {statusFilter === "ALL" ? "Todavía no tienes ninguna oferta" : `No tienes ofertas ${STATUS_CONFIG[statusFilter].label.toLowerCase()}s`}
                        </p>
                        {statusFilter === "ALL" && (
                            <button
                                onClick={() => navigate("/offers/new")}
                                className="mt-4 px-5 py-2.5 bg-primary text-white text-sm font-bold rounded-xl shadow-lg shadow-primary/20"
                            >
                                Crear primera oferta
                            </button>
                        )}
                    </div>
                )}

                {!isLoading && !isError && filtered.length > 0 && (
                    <div className="flex flex-col gap-3">
                        {filtered.map(offer => (
                            <OfferRow
                                key={offer.id}
                                offer={offer}
                                isEditing={editingId === offer.id}
                                onToggleEdit={() => setEditingId(id => id === offer.id ? null : offer.id)}
                                onUpdate={patch => updateMut.mutate({ id: offer.id, patch })}
                                onView={() => navigate(`/offer/${offer.slug}`)}
                            />
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

function OfferRow({
    offer, isEditing, onToggleEdit, onUpdate, onView,
}: {
    offer: MyOfferItem;
    isEditing: boolean;
    onToggleEdit: () => void;
    onUpdate: (patch: Parameters<typeof updateOffer>[2]) => void;
    onView: () => void;
}) {
    const [editTab, setEditTab] = useState<"status" | "data">("status");

    // Local form state for data editing
    const [formTitle, setFormTitle] = useState(offer.title);
    const [formDesc, setFormDesc] = useState(offer.description ?? "");
    const [formPayment, setFormPayment] = useState<"MONEY" | "POINTS">(offer.pointsCost > 0 ? "POINTS" : "MONEY");
    const [formPrice, setFormPrice] = useState(offer.price);
    const [formPoints, setFormPoints] = useState(offer.pointsCost);
    const [formQty, setFormQty] = useState(offer.quantity);
    const [formImage, setFormImage] = useState(offer.image ?? "");
    const [formDiscount, setFormDiscount] = useState<"NONE" | "AMOUNT" | "PERCENT">(offer.discountType as "NONE" | "AMOUNT" | "PERCENT");

    // Reset local form when panel opens
    const handleToggleEdit = () => {
        setFormTitle(offer.title);
        setFormDesc(offer.description ?? "");
        setFormPayment(offer.pointsCost > 0 ? "POINTS" : "MONEY");
        setFormPrice(offer.price);
        setFormPoints(offer.pointsCost);
        setFormQty(offer.quantity);
        setFormImage(offer.image ?? "");
        setFormDiscount(offer.discountType as "NONE" | "AMOUNT" | "PERCENT");
        setEditTab("status");
        onToggleEdit();
    };

    const handleSaveData = () => {
        const patch: Parameters<typeof updateOffer>[2] = {
            title: formTitle.trim() || undefined,
            description: formDesc.trim() || undefined,
            price: formPayment === "MONEY" ? formPrice : "0.00",
            pointsCost: formPayment === "POINTS" ? formPoints : 0,
            quantity: formQty,
            image: formImage.trim() || undefined,
            discountType: formPayment === "MONEY" ? formDiscount : "NONE",
        };
        onUpdate(patch);
        onToggleEdit();
    };

    const canSave = formTitle.trim() !== "" &&
        (formPayment === "MONEY" ? /^\d+(\.\d{1,2})?$/.test(formPrice) && parseFloat(formPrice) > 0 : formPoints > 0);

    const st = STATUS_CONFIG[offer.status as keyof typeof STATUS_CONFIG] ?? STATUS_CONFIG.DRAFT;
    const isPoints = offer.pointsCost > 0;
    const priceFormatted = isPoints
        ? `${offer.pointsCost} pts`
        : new Intl.NumberFormat("es-ES", { style: "currency", currency: offer.currency }).format(parseFloat(offer.price));

    return (
        <div className="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden transition-all hover:shadow-md">
            <div className="flex items-stretch">
                {/* Thumbnail */}
                <div className="w-20 sm:w-28 shrink-0 bg-slate-100 dark:bg-slate-800 cursor-pointer overflow-hidden" onClick={onView}>
                    <img
                        src={offer.image || getFallbackImage(offer.id, "offer")}
                        alt={offer.title}
                        className="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                    />
                </div>

                {/* Info */}
                <div className="flex-1 min-w-0 p-3 sm:p-4">
                    <div className="flex items-start justify-between gap-2">
                        <div className="min-w-0 flex-1">
                            <button
                                onClick={onView}
                                className="text-sm sm:text-base font-bold text-slate-900 dark:text-white hover:text-primary transition-colors text-left truncate block max-w-full"
                            >
                                {offer.title}
                            </button>
                            <div className="flex items-center gap-2 mt-1 flex-wrap">
                                <span className={`text-sm font-black flex items-center gap-1 ${isPoints ? "text-amber-500" : "text-primary"}`}>
                                    {isPoints && <span className="material-symbols-outlined !text-sm">stars</span>}
                                    {priceFormatted}
                                </span>
                                {offer.quantity > 0 && (
                                    <span className="text-[11px] font-semibold text-slate-400 flex items-center gap-1">
                                        <span className="material-symbols-outlined !text-[13px]">inventory_2</span>
                                        {offer.quantity} uds.
                                    </span>
                                )}
                                {offer.discountType !== "NONE" && (
                                    <span className="text-[10px] font-bold px-1.5 py-0.5 bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800 rounded">
                                        {offer.discountType === "PERCENT" ? "% dto" : "€ dto"}
                                    </span>
                                )}
                                <span className="text-[11px] text-slate-400">
                                    {new Date(offer.createdAt).toLocaleDateString("es-ES", { day: "2-digit", month: "short", year: "numeric" })}
                                </span>
                            </div>
                        </div>
                        <button
                            onClick={handleToggleEdit}
                            className="shrink-0 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
                        >
                            <span className="material-symbols-outlined !text-base">{isEditing ? "expand_less" : "edit"}</span>
                        </button>
                    </div>

                    {/* Badges */}
                    <div className="flex items-center gap-2 mt-2">
                        <span className={`flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-widest ${st.color}`}>
                            <span className={`w-1.5 h-1.5 rounded-full ${st.dot}`} />
                            {st.label}
                        </span>
                        {!offer.isActive && (
                            <span className="flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 bg-red-50 dark:bg-red-950/20 text-red-500 border border-red-200 dark:border-red-800 rounded-full">
                                <span className="material-symbols-outlined !text-[11px]">visibility_off</span>
                                Desactivada
                            </span>
                        )}
                    </div>
                </div>
            </div>

            {/* Edit panel */}
            {isEditing && (
                <div className="border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                    {/* Tabs */}
                    <div className="flex border-b border-slate-100 dark:border-slate-800">
                        {(["status", "data"] as const).map(tab => (
                            <button
                                key={tab}
                                onClick={() => setEditTab(tab)}
                                className={`flex items-center gap-1.5 px-4 py-2.5 text-[11px] font-black uppercase tracking-widest border-b-2 transition-all ${editTab === tab
                                        ? "border-primary text-primary"
                                        : "border-transparent text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                                    }`}
                            >
                                <span className="material-symbols-outlined !text-sm">
                                    {tab === "status" ? "toggle_on" : "edit_note"}
                                </span>
                                {tab === "status" ? "Estado" : "Datos"}
                            </button>
                        ))}
                        <button
                            onClick={onView}
                            className="ml-auto flex items-center gap-1 px-4 py-2.5 text-[11px] font-bold text-primary hover:bg-primary/5 transition-all"
                        >
                            <span className="material-symbols-outlined !text-sm">open_in_new</span>
                            Ver
                        </button>
                    </div>

                    {/* Status tab */}
                    {editTab === "status" && (
                        <div className="flex flex-wrap gap-4 items-center px-4 py-3">
                            <div className="flex flex-col gap-1">
                                <label className="text-[10px] font-black uppercase tracking-widest text-slate-400">Estado</label>
                                <div className="flex gap-1.5">
                                    {(["PUBLISHED", "DRAFT", "ARCHIVED"] as const).map(s => {
                                        const cfg = STATUS_CONFIG[s];
                                        return (
                                            <button
                                                key={s}
                                                onClick={() => onUpdate({ status: s })}
                                                className={`flex items-center gap-1.5 px-2.5 py-1.5 text-[11px] font-bold border rounded-lg transition-all ${offer.status === s
                                                        ? `${cfg.bg} ${cfg.color} shadow-sm`
                                                        : "bg-white dark:bg-slate-900 text-slate-400 border-slate-200 dark:border-slate-700 hover:border-slate-300"
                                                    }`}
                                            >
                                                <span className={`w-1.5 h-1.5 rounded-full ${cfg.dot}`} />
                                                {cfg.label}
                                            </button>
                                        );
                                    })}
                                </div>
                            </div>
                            <div className="flex flex-col gap-1">
                                <label className="text-[10px] font-black uppercase tracking-widest text-slate-400">Visibilidad</label>
                                <button
                                    onClick={() => onUpdate({ isActive: !offer.isActive })}
                                    className={`flex items-center gap-1.5 px-2.5 py-1.5 text-[11px] font-bold border rounded-lg transition-all ${offer.isActive
                                            ? "bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800"
                                            : "bg-red-50 dark:bg-red-950/20 text-red-500 border-red-200 dark:border-red-800"
                                        }`}
                                >
                                    <span className="material-symbols-outlined !text-[13px]">{offer.isActive ? "visibility" : "visibility_off"}</span>
                                    {offer.isActive ? "Activa" : "Desactivada"}
                                </button>
                            </div>
                        </div>
                    )}

                    {/* Data tab */}
                    {editTab === "data" && (
                        <div className="px-4 py-4 flex flex-col gap-3">
                            {/* Title */}
                            <div>
                                <label className="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Título *</label>
                                <input
                                    type="text"
                                    value={formTitle}
                                    onChange={e => setFormTitle(e.target.value)}
                                    className="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                                />
                            </div>
                            {/* Description */}
                            <div>
                                <label className="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Descripción</label>
                                <textarea
                                    value={formDesc}
                                    onChange={e => setFormDesc(e.target.value)}
                                    rows={2}
                                    className="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all resize-none"
                                />
                            </div>
                            {/* Payment type */}
                            <div>
                                <label className="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Tipo de pago</label>
                                <div className="flex gap-2">
                                    {(["MONEY", "POINTS"] as const).map(pt => (
                                        <button
                                            key={pt}
                                            onClick={() => setFormPayment(pt)}
                                            className={`flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold border rounded-lg transition-all ${formPayment === pt
                                                    ? "bg-primary/5 border-primary/40 text-primary"
                                                    : "bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-400 hover:border-slate-300"
                                                }`}
                                        >
                                            <span className="material-symbols-outlined !text-sm">{pt === "MONEY" ? "euro" : "stars"}</span>
                                            {pt === "MONEY" ? "Dinero" : "Puntos"}
                                        </button>
                                    ))}
                                </div>
                            </div>
                            {/* Price / Points + Qty */}
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    {formPayment === "MONEY" ? (
                                        <>
                                            <label className="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Precio (€) *</label>
                                            <div className="relative">
                                                <span className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">€</span>
                                                <input type="text" value={formPrice} onChange={e => setFormPrice(e.target.value)}
                                                    className="w-full pl-7 pr-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all" />
                                            </div>
                                        </>
                                    ) : (
                                        <>
                                            <label className="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Puntos *</label>
                                            <div className="relative">
                                                <span className="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-amber-400 !text-sm">stars</span>
                                                <input type="number" min={1} value={formPoints} onChange={e => setFormPoints(Math.max(1, parseInt(e.target.value) || 1))}
                                                    className="w-full pl-8 pr-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all" />
                                            </div>
                                        </>
                                    )}
                                </div>
                                <div>
                                    <label className="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Stock (0=∞)</label>
                                    <input type="number" min={0} value={formQty} onChange={e => setFormQty(Math.max(0, parseInt(e.target.value) || 0))}
                                        className="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all" />
                                </div>
                            </div>
                            {/* Discount type (MONEY only) */}
                            {formPayment === "MONEY" && (
                                <div>
                                    <label className="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Descuento</label>
                                    <div className="flex gap-1.5">
                                        {(["NONE", "AMOUNT", "PERCENT"] as const).map(d => (
                                            <button key={d} onClick={() => setFormDiscount(d)}
                                                className={`flex items-center gap-1 px-2.5 py-1.5 text-[11px] font-bold border rounded-lg transition-all ${formDiscount === d
                                                        ? "bg-primary/5 border-primary/40 text-primary"
                                                        : "bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-400 hover:border-slate-300"
                                                    }`}>
                                                <span className="material-symbols-outlined !text-sm">{d === "NONE" ? "sell" : d === "AMOUNT" ? "euro" : "percent"}</span>
                                                {d === "NONE" ? "Sin dto" : d === "AMOUNT" ? "Fijo" : "%"}
                                            </button>
                                        ))}
                                    </div>
                                </div>
                            )}
                            {/* Image */}
                            <div>
                                <label className="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">URL imagen</label>
                                <input type="url" value={formImage} onChange={e => setFormImage(e.target.value)} placeholder="https://..."
                                    className="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all" />
                            </div>
                            {/* Save */}
                            <div className="flex gap-2 pt-1">
                                <button onClick={handleToggleEdit}
                                    className="flex-1 py-2 text-[12px] font-bold text-slate-500 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                                    Cancelar
                                </button>
                                <button disabled={!canSave} onClick={handleSaveData}
                                    className="flex-1 py-2 text-[12px] font-bold text-white bg-primary hover:bg-blue-600 disabled:opacity-40 disabled:cursor-not-allowed rounded-lg shadow-sm shadow-primary/20 transition-all">
                                    Guardar cambios
                                </button>
                            </div>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
