import { useNavigate } from "react-router-dom";
import type { OfferListItem } from "../domain/types";

export function OfferCard({ item }: { item: OfferListItem }) {
    const navigate = useNavigate();

    return (
        <div
            onClick={() => navigate(`/offer/${item.slug}`)}
            className="group bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden hover:shadow-xl hover:shadow-slate-200/50 dark:hover:shadow-none transition-all cursor-pointer"
        >
            <div className="relative aspect-[16/9] overflow-hidden bg-slate-100 dark:bg-slate-800">
                {item.image ? (
                    <img
                        className="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                        src={item.image}
                        alt={item.title}
                    />
                ) : (
                    <div className="absolute inset-0 flex items-center justify-center text-slate-300">
                        <span className="material-symbols-outlined !text-4xl">image</span>
                    </div>
                )}
                <div className="absolute top-3 left-3">
                    <span className="px-2 py-1 bg-white/90 dark:bg-slate-900/90 backdrop-blur rounded text-[10px] font-bold uppercase tracking-widest text-primary shadow-sm">
                        {item.discountType}
                    </span>
                </div>
                <div className="absolute bottom-3 right-3">
                    <span className="px-2 py-1 bg-primary text-white rounded text-[10px] font-bold shadow-lg">
                        {item.pointsCost} VAC
                    </span>
                </div>
            </div>

            <div className="p-4">
                <div className="flex justify-between items-start mb-1">
                    <h4 className="font-bold text-slate-900 dark:text-white group-hover:text-primary transition-colors truncate pr-2">
                        {item.title}
                    </h4>
                    <span className="font-extrabold text-primary text-sm whitespace-nowrap">
                        {item.price} {item.currency}
                    </span>
                </div>
                <p className="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-1 mb-3">
                    {item.description || "Sin descripción disponible"}
                </p>
                <div className="flex items-center justify-between text-[10px] font-bold uppercase tracking-tighter text-slate-400">
                    <div className="flex items-center gap-1">
                        <span className="material-symbols-outlined !text-[14px]">local_mall</span>
                        Stock: {item.quantity}
                    </div>
                    <button className="text-primary hover:underline transition-all">Ver más</button>
                </div>
            </div>
        </div>
    );
}
