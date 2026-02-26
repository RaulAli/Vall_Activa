import { useState, useMemo } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { useNavigate } from "react-router-dom";
import { useAuthStore } from "../store/authStore";
import { http } from "../shared/api/http";
import { endpoints } from "../shared/api/endpoints";

const PAGE_SIZE = 20;


interface AdminStats {
    users: { total: number; active: number };
    routes: { total: number; public: number };
    offers: { total: number; active: number };
    businesses: { total: number };
    sports: { total: number };
}

interface AdminUser {
    id: string; email: string; role: string; isActive: boolean; createdAt: string;
}
interface AdminRoute {
    id: string; title: string; slug: string; visibility: string; status: string;
    distanceM: number; elevationGainM: number; durationMin: number | null;
    difficulty: string | null; routeType: string | null;
    createdByUserId: string; adminDisabled: boolean;
    sport: { code: string; name: string } | null; createdAt: string;
}
interface AdminOffer {
    id: string; title: string; slug: string; businessId: string; price: string;
    currency: string; discountType: string; status: string; isActive: boolean;
    quantity: number; pointsCost: number; adminDisabled: boolean; createdAt: string;
}
interface AdminBusiness {
    userId: string; slug: string; name: string; profileIcon: string | null;
    lat: number | null; lng: number | null; isActive: boolean; createdAt: string;
}
interface AdminSport {
    id: string; code: string; name: string; isActive: boolean; createdAt: string;
}

// --- Helpers -----------------------------------------------------------------

type Tab = "dashboard" | "users" | "routes" | "offers" | "businesses" | "sports";

const ROLE_COLORS: Record<string, string> = {
    ROLE_ADMIN: "bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400",
    ROLE_BUSINESS: "bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400",
    ROLE_GUIDE: "bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400",
    ROLE_ATHLETE: "bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400",
};

const VISIBILITY_COLORS: Record<string, string> = {
    PUBLIC: "bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400",
    UNLISTED: "bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400",
    PRIVATE: "bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400",
};

const DIFFICULTY_COLORS: Record<string, string> = {
    easy: "bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400",
    medium: "bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400",
    hard: "bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400",
    EASY: "bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400",
    MEDIUM: "bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400",
    HARD: "bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400",
};

function Badge({ text, colorClass }: { text: string; colorClass: string }) {
    return (
        <span className={`px-2 py-0.5 rounded-full text-[11px] font-black uppercase tracking-wider ${colorClass}`}>
            {text}
        </span>
    );
}

function StatCard({ icon, label, value, sub, color }: {
    icon: string; label: string; value: number; sub?: string; color: string;
}) {
    return (
        <div className="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 flex items-center gap-5 shadow-sm">
            <div className={`w-12 h-12 rounded-xl flex items-center justify-center ${color}`}>
                <span className="material-symbols-outlined !text-2xl">{icon}</span>
            </div>
            <div>
                <p className="text-2xl font-black text-slate-900 dark:text-white">{value}</p>
                <p className="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">{label}</p>
                {sub && <p className="text-xs text-slate-400 mt-0.5">{sub}</p>}
            </div>
        </div>
    );
}

function SearchInput({ value, onChange, placeholder }: {
    value: string; onChange: (v: string) => void; placeholder: string;
}) {
    return (
        <div className="relative">
            <span className="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 !text-lg">search</span>
            <input
                value={value}
                onChange={e => onChange(e.target.value)}
                placeholder={placeholder}
                className="pl-9 pr-4 py-2 text-sm font-medium bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/40 w-56"
            />
        </div>
    );
}

function SelectFilter({ value, onChange, options, placeholder }: {
    value: string; onChange: (v: string) => void;
    options: { value: string; label: string }[]; placeholder: string;
}) {
    return (
        <select
            value={value}
            onChange={e => onChange(e.target.value)}
            className="py-2 pl-3 pr-8 text-sm font-medium bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/40 appearance-none cursor-pointer"
        >
            <option value="">{placeholder}</option>
            {options.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
        </select>
    );
}

function DeleteButton({ onConfirm }: { onConfirm: () => void }) {
    const [confirm, setConfirm] = useState(false);
    return confirm ? (
        <div className="flex items-center gap-1">
            <button onClick={onConfirm} className="px-2 py-1 text-[11px] font-black bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">Confirmar</button>
            <button onClick={() => setConfirm(false)} className="px-2 py-1 text-[11px] font-black bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-lg">Cancelar</button>
        </div>
    ) : (
        <button onClick={() => setConfirm(true)} className="p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
            <span className="material-symbols-outlined !text-base">delete</span>
        </button>
    );
}

function Pagination({ page, totalPages, onChange }: {
    page: number; totalPages: number; onChange: (p: number) => void;
}) {
    if (totalPages <= 1) return null;
    return (
        <div className="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800">
            <p className="text-xs font-bold text-slate-500 dark:text-slate-400">
                Pagina {page} de {totalPages}
            </p>
            <div className="flex items-center gap-1">
                <button
                    onClick={() => onChange(1)}
                    disabled={page === 1}
                    className="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                >
                    <span className="material-symbols-outlined !text-base">first_page</span>
                </button>
                <button
                    onClick={() => onChange(page - 1)}
                    disabled={page === 1}
                    className="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                >
                    <span className="material-symbols-outlined !text-base">chevron_left</span>
                </button>
                {Array.from({ length: Math.min(5, totalPages) }, (_, i) => {
                    const start = Math.max(1, Math.min(page - 2, totalPages - 4));
                    const p = start + i;
                    return p <= totalPages ? (
                        <button
                            key={p}
                            onClick={() => onChange(p)}
                            className={`w-8 h-8 rounded-lg text-sm font-black transition-colors ${p === page
                                ? "bg-primary text-white shadow-sm"
                                : "text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800"
                                }`}
                        >
                            {p}
                        </button>
                    ) : null;
                })}
                <button
                    onClick={() => onChange(page + 1)}
                    disabled={page === totalPages}
                    className="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                >
                    <span className="material-symbols-outlined !text-base">chevron_right</span>
                </button>
                <button
                    onClick={() => onChange(totalPages)}
                    disabled={page === totalPages}
                    className="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                >
                    <span className="material-symbols-outlined !text-base">last_page</span>
                </button>
            </div>
        </div>
    );
}

// --- Section: Dashboard -------------------------------------------------------

function DashboardSection({ stats }: { stats: AdminStats }) {
    return (
        <div className="space-y-8">
            <div>
                <h2 className="text-2xl font-black text-slate-900 dark:text-white mb-1">Panel de control</h2>
                <p className="text-slate-500 dark:text-slate-400 font-medium">Resumen general de la plataforma.</p>
            </div>
            <div className="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                <StatCard icon="group" label="Usuarios" value={stats.users.total} sub={`${stats.users.active} activos`} color="bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400" />
                <StatCard icon="route" label="Rutas" value={stats.routes.total} sub={`${stats.routes.public} publicas`} color="bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400" />
                <StatCard icon="storefront" label="Ofertas" value={stats.offers.total} sub={`${stats.offers.active} activas`} color="bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400" />
                <StatCard icon="business" label="Empresas" value={stats.businesses.total} color="bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400" />
                <StatCard icon="sports" label="Deportes" value={stats.sports.total} color="bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400" />
            </div>
            {/* Ratios */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div className="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm">
                    <p className="text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Actividad usuarios</p>
                    <div className="flex items-end gap-2 mb-2">
                        <span className="text-3xl font-black text-slate-900 dark:text-white">
                            {stats.users.total > 0 ? Math.round((stats.users.active / stats.users.total) * 100) : 0}%
                        </span>
                        <span className="text-sm font-bold text-slate-500 mb-1">activos</span>
                    </div>
                    <div className="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div
                            className="h-full bg-blue-500 rounded-full transition-all"
                            style={{ width: `${stats.users.total > 0 ? (stats.users.active / stats.users.total) * 100 : 0}%` }}
                        />
                    </div>
                </div>
                <div className="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm">
                    <p className="text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Rutas publicas</p>
                    <div className="flex items-end gap-2 mb-2">
                        <span className="text-3xl font-black text-slate-900 dark:text-white">
                            {stats.routes.total > 0 ? Math.round((stats.routes.public / stats.routes.total) * 100) : 0}%
                        </span>
                        <span className="text-sm font-bold text-slate-500 mb-1">visibles</span>
                    </div>
                    <div className="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div
                            className="h-full bg-emerald-500 rounded-full transition-all"
                            style={{ width: `${stats.routes.total > 0 ? (stats.routes.public / stats.routes.total) * 100 : 0}%` }}
                        />
                    </div>
                </div>
                <div className="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm">
                    <p className="text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Ofertas activas</p>
                    <div className="flex items-end gap-2 mb-2">
                        <span className="text-3xl font-black text-slate-900 dark:text-white">
                            {stats.offers.total > 0 ? Math.round((stats.offers.active / stats.offers.total) * 100) : 0}%
                        </span>
                        <span className="text-sm font-bold text-slate-500 mb-1">en activo</span>
                    </div>
                    <div className="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div
                            className="h-full bg-amber-500 rounded-full transition-all"
                            style={{ width: `${stats.offers.total > 0 ? (stats.offers.active / stats.offers.total) * 100 : 0}%` }}
                        />
                    </div>
                </div>
            </div>
        </div>
    );
}

// --- Section: Users -----------------------------------------------------------

function UsersSection({ token }: { token: string }) {
    const qc = useQueryClient();
    const [q, setQ] = useState("");
    const [roleFilter, setRoleFilter] = useState("");
    const [statusFilter, setStatusFilter] = useState("");
    const [page, setPage] = useState(1);

    const { data: users = [], isLoading } = useQuery<AdminUser[]>({
        queryKey: ["admin", "users"],
        queryFn: () => http<AdminUser[]>("GET", endpoints.admin.users, { headers: { Authorization: `Bearer ${token}` } }),
    });

    const toggleMutation = useMutation({
        mutationFn: (id: string) => http<{ isActive: boolean }>("PATCH", endpoints.admin.toggleUser(id), { headers: { Authorization: `Bearer ${token}` } }),
        onSuccess: () => qc.invalidateQueries({ queryKey: ["admin", "users"] }),
    });

    const deleteMutation = useMutation({
        mutationFn: (id: string) => http<void>("DELETE", endpoints.admin.deleteUser(id), { headers: { Authorization: `Bearer ${token}` } }),
        onSuccess: () => { qc.invalidateQueries({ queryKey: ["admin", "users"] }); qc.invalidateQueries({ queryKey: ["admin", "stats"] }); },
    });

    const filtered = useMemo(() => {
        return users.filter(u => {
            if (q && !u.email.toLowerCase().includes(q.toLowerCase()) && !u.id.toLowerCase().includes(q.toLowerCase())) return false;
            if (roleFilter && u.role !== roleFilter) return false;
            if (statusFilter === "active" && !u.isActive) return false;
            if (statusFilter === "inactive" && u.isActive) return false;
            return true;
        });
    }, [users, q, roleFilter, statusFilter]);

    const totalPages = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
    const paginated = filtered.slice((page - 1) * PAGE_SIZE, page * PAGE_SIZE);

    const roleOptions = [
        { value: "ROLE_ADMIN", label: "Admin" },
        { value: "ROLE_BUSINESS", label: "Business" },
        { value: "ROLE_GUIDE", label: "Guide" },
        { value: "ROLE_ATHLETE", label: "Athlete" },
    ];

    const hasFilters = q || roleFilter || statusFilter;

    return (
        <div className="space-y-6">
            <div>
                <h2 className="text-2xl font-black text-slate-900 dark:text-white mb-1">Usuarios</h2>
                <p className="text-slate-500 dark:text-slate-400 font-medium">
                    {filtered.length} de {users.length} usuarios
                    {hasFilters ? " (filtrados)" : " registrados"}
                </p>
            </div>
            {/* Filters */}
            <div className="flex flex-wrap gap-3 items-center bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4 shadow-sm">
                <SearchInput value={q} onChange={v => { setQ(v); setPage(1); }} placeholder="Buscar email o ID..." />
                <SelectFilter
                    value={roleFilter}
                    onChange={v => { setRoleFilter(v); setPage(1); }}
                    options={roleOptions}
                    placeholder="Todos los roles"
                />
                <SelectFilter
                    value={statusFilter}
                    onChange={v => { setStatusFilter(v); setPage(1); }}
                    options={[{ value: "active", label: "Activos" }, { value: "inactive", label: "Inactivos" }]}
                    placeholder="Todos los estados"
                />
                {hasFilters && (
                    <button
                        onClick={() => { setQ(""); setRoleFilter(""); setStatusFilter(""); setPage(1); }}
                        className="flex items-center gap-1 px-3 py-2 text-xs font-black text-slate-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors"
                    >
                        <span className="material-symbols-outlined !text-sm">filter_list_off</span>
                        Limpiar
                    </button>
                )}
                <div className="ml-auto text-xs font-bold text-slate-400">
                    {paginated.length} mostrados · {PAGE_SIZE}/pagina
                </div>
            </div>
            <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden shadow-sm">
                <div className="overflow-x-auto">
                    <table className="w-full text-sm min-w-[700px]">
                        <thead>
                            <tr className="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                                <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Email</th>
                                <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">ID</th>
                                <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Rol</th>
                                <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Estado</th>
                                <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Registro</th>
                                <th className="px-5 py-3 text-right font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {isLoading && [...Array(5)].map((_, i) => (
                                <tr key={i} className="border-b border-slate-50 dark:border-slate-800/50">
                                    <td colSpan={6} className="px-5 py-3"><div className="h-4 bg-slate-100 dark:bg-slate-800 rounded animate-pulse w-48" /></td>
                                </tr>
                            ))}
                            {paginated.map(user => (
                                <tr key={user.id} className="border-b border-slate-50 dark:border-slate-800/50 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td className="px-5 py-3 font-semibold text-slate-900 dark:text-white">{user.email}</td>
                                    <td className="px-5 py-3">
                                        <span className="font-mono text-[11px] text-slate-400 bg-slate-50 dark:bg-slate-800 px-1.5 py-0.5 rounded" title={user.id}>
                                            {user.id.slice(0, 8)}...
                                        </span>
                                    </td>
                                    <td className="px-5 py-3">
                                        <Badge text={user.role.replace("ROLE_", "")} colorClass={ROLE_COLORS[user.role] ?? "bg-slate-100 text-slate-600"} />
                                    </td>
                                    <td className="px-5 py-3">
                                        <Badge
                                            text={user.isActive ? "Activo" : "Inactivo"}
                                            colorClass={user.isActive
                                                ? "bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400"
                                                : "bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400"}
                                        />
                                    </td>
                                    <td className="px-5 py-3 text-slate-500 dark:text-slate-400 text-xs whitespace-nowrap">
                                        {new Date(user.createdAt).toLocaleDateString("es-ES", { day: "2-digit", month: "short", year: "numeric" })}
                                    </td>
                                    <td className="px-5 py-3">
                                        <div className="flex items-center gap-1 justify-end">
                                            <button
                                                onClick={() => toggleMutation.mutate(user.id)}
                                                disabled={toggleMutation.isPending}
                                                title={user.isActive ? "Desactivar cuenta" : "Activar cuenta"}
                                                className="p-1.5 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors"
                                            >
                                                <span className="material-symbols-outlined !text-base">{user.isActive ? "toggle_on" : "toggle_off"}</span>
                                            </button>
                                            <DeleteButton onConfirm={() => deleteMutation.mutate(user.id)} />
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {!isLoading && filtered.length === 0 && (
                                <tr><td colSpan={6} className="px-5 py-10 text-center text-slate-400 font-bold">Sin resultados para los filtros aplicados</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
                <div className="px-5 py-3">
                    <Pagination page={page} totalPages={totalPages} onChange={setPage} />
                </div>
            </div>
        </div>
    );
}

// --- Section: Routes ----------------------------------------------------------

function RoutesSection({ token }: { token: string }) {
    const qc = useQueryClient();
    const [q, setQ] = useState("");
    const [visibilityFilter, setVisibilityFilter] = useState("");
    const [sportFilter, setSportFilter] = useState("");
    const [difficultyFilter, setDifficultyFilter] = useState("");
    const [page, setPage] = useState(1);

    const { data: routes = [], isLoading } = useQuery<AdminRoute[]>({
        queryKey: ["admin", "routes"],
        queryFn: () => http<AdminRoute[]>("GET", endpoints.admin.routes, { headers: { Authorization: `Bearer ${token}` } }),
    });

    const toggleMutation = useMutation({
        mutationFn: (id: string) => http<{ id: string; adminDisabled: boolean }>("PATCH", endpoints.admin.toggleRoute(id), { headers: { Authorization: `Bearer ${token}` } }),
        onSuccess: () => { qc.invalidateQueries({ queryKey: ["admin", "routes"] }); qc.invalidateQueries({ queryKey: ["admin", "stats"] }); },
    });

    const sportOptions = useMemo(() => {
        const codes = [...new Set(routes.map(r => r.sport?.code).filter(Boolean))] as string[];
        return codes.sort().map(c => ({ value: c, label: c }));
    }, [routes]);

    const filtered = useMemo(() => {
        return routes.filter(r => {
            if (q && !r.title.toLowerCase().includes(q.toLowerCase()) && !r.slug.toLowerCase().includes(q.toLowerCase())) return false;
            if (visibilityFilter && r.visibility !== visibilityFilter) return false;
            if (sportFilter && r.sport?.code !== sportFilter) return false;
            if (difficultyFilter && r.difficulty !== difficultyFilter) return false;
            return true;
        });
    }, [routes, q, visibilityFilter, sportFilter, difficultyFilter]);

    const totalPages = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
    const paginated = filtered.slice((page - 1) * PAGE_SIZE, page * PAGE_SIZE);

    const hasFilters = q || visibilityFilter || sportFilter || difficultyFilter;

    return (
        <div className="space-y-6">
            <div>
                <h2 className="text-2xl font-black text-slate-900 dark:text-white mb-1">Rutas</h2>
                <p className="text-slate-500 dark:text-slate-400 font-medium">
                    {filtered.length} de {routes.length} rutas{hasFilters ? " (filtradas)" : " registradas"}
                </p>
            </div>
            {/* Filters */}
            <div className="flex flex-wrap gap-3 items-center bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4 shadow-sm">
                <SearchInput value={q} onChange={v => { setQ(v); setPage(1); }} placeholder="Buscar titulo o slug..." />
                <SelectFilter
                    value={visibilityFilter}
                    onChange={v => { setVisibilityFilter(v); setPage(1); }}
                    options={[
                        { value: "PUBLIC", label: "Publica" },
                        { value: "UNLISTED", label: "No listada" },
                        { value: "PRIVATE", label: "Privada" },
                    ]}
                    placeholder="Toda visibilidad"
                />
                <SelectFilter
                    value={sportFilter}
                    onChange={v => { setSportFilter(v); setPage(1); }}
                    options={sportOptions}
                    placeholder="Todos los deportes"
                />
                <SelectFilter
                    value={difficultyFilter}
                    onChange={v => { setDifficultyFilter(v); setPage(1); }}
                    options={[
                        { value: "EASY", label: "Facil" },
                        { value: "MEDIUM", label: "Media" },
                        { value: "HARD", label: "Dificil" },
                    ]}
                    placeholder="Toda dificultad"
                />
                {hasFilters && (
                    <button
                        onClick={() => { setQ(""); setVisibilityFilter(""); setSportFilter(""); setDifficultyFilter(""); setPage(1); }}
                        className="flex items-center gap-1 px-3 py-2 text-xs font-black text-slate-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors"
                    >
                        <span className="material-symbols-outlined !text-sm">filter_list_off</span>
                        Limpiar
                    </button>
                )}
                <div className="ml-auto text-xs font-bold text-slate-400">
                    {paginated.length} mostradas · {PAGE_SIZE}/pagina
                </div>
            </div>
            <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden shadow-sm">
                <div className="overflow-x-auto">
                    <table className="w-full text-sm min-w-[900px]">
                        <thead>
                            <tr className="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                                <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Titulo</th>
                                <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Deporte</th>
                                <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Visibilidad</th>
                                <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Dificultad</th>
                                <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Distancia</th>
                                <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Desnivel</th>
                                <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Duracion</th>
                                <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Fecha</th>
                                <th className="px-5 py-3 text-right font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {isLoading && [...Array(5)].map((_, i) => (
                                <tr key={i} className="border-b border-slate-50 dark:border-slate-800/50">
                                    <td colSpan={9} className="px-5 py-3"><div className="h-4 bg-slate-100 dark:bg-slate-800 rounded animate-pulse w-48" /></td>
                                </tr>
                            ))}
                            {paginated.map(route => (
                                <tr key={route.id} className={`border-b border-slate-50 dark:border-slate-800/50 transition-colors ${route.adminDisabled ? "opacity-50 bg-red-50/30 dark:bg-red-900/10" : "hover:bg-slate-50/50 dark:hover:bg-slate-800/30"}`}>
                                    <td className="px-5 py-3 max-w-[200px]">
                                        <div>
                                            <p className={`font-semibold truncate ${route.adminDisabled ? "line-through text-slate-400" : "text-slate-900 dark:text-white"}`} title={route.title}>{route.title}</p>
                                            <p className="text-[11px] font-mono text-slate-400 truncate">{route.slug}</p>
                                        </div>
                                    </td>
                                    <td className="px-5 py-3">
                                        {route.sport
                                            ? <Badge text={route.sport.code} colorClass="bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400" />
                                            : <span className="text-slate-400">-</span>}
                                    </td>
                                    <td className="px-5 py-3">
                                        <Badge text={route.visibility} colorClass={VISIBILITY_COLORS[route.visibility] ?? "bg-slate-100 text-slate-600"} />
                                    </td>
                                    <td className="px-5 py-3">
                                        {route.difficulty
                                            ? <Badge text={route.difficulty} colorClass={DIFFICULTY_COLORS[route.difficulty] ?? "bg-slate-100 text-slate-600"} />
                                            : <span className="text-slate-400">-</span>}
                                    </td>
                                    <td className="px-5 py-3 text-slate-600 dark:text-slate-300 text-xs font-bold whitespace-nowrap">
                                        {(route.distanceM / 1000).toFixed(1)} km
                                    </td>
                                    <td className="px-5 py-3 text-slate-600 dark:text-slate-300 text-xs font-bold whitespace-nowrap">
                                        +{route.elevationGainM} m
                                    </td>
                                    <td className="px-5 py-3 text-slate-500 dark:text-slate-400 text-xs whitespace-nowrap">
                                        {route.durationMin != null ? `${route.durationMin} min` : "-"}
                                    </td>
                                    <td className="px-5 py-3 text-slate-500 dark:text-slate-400 text-xs whitespace-nowrap">
                                        {new Date(route.createdAt).toLocaleDateString("es-ES", { day: "2-digit", month: "short", year: "numeric" })}
                                    </td>
                                    <td className="px-5 py-3">
                                        <div className="flex items-center gap-1 justify-end">
                                            {!route.adminDisabled && (
                                                <a
                                                    href={`/route/${route.slug}`}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="p-1.5 rounded-lg text-slate-400 hover:text-primary hover:bg-primary/10 transition-colors"
                                                    title="Ver ruta"
                                                >
                                                    <span className="material-symbols-outlined !text-base">open_in_new</span>
                                                </a>
                                            )}
                                            <button
                                                onClick={() => toggleMutation.mutate(route.id)}
                                                disabled={toggleMutation.isPending}
                                                title={route.adminDisabled ? "Reactivar ruta" : "Desactivar ruta (admin)"}
                                                className={`p-1.5 rounded-lg transition-colors ${route.adminDisabled ? "text-green-500 hover:bg-green-50 dark:hover:bg-green-900/20" : "text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20"}`}
                                            >
                                                <span className="material-symbols-outlined !text-base">{route.adminDisabled ? "lock_open" : "block"}</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {!isLoading && filtered.length === 0 && (
                                <tr><td colSpan={9} className="px-5 py-10 text-center text-slate-400 font-bold">Sin resultados para los filtros aplicados</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
                <div className="px-5 py-3">
                    <Pagination page={page} totalPages={totalPages} onChange={setPage} />
                </div>
            </div>
        </div>
    );
}

// --- Section: Offers ----------------------------------------------------------

function OffersSection({ token }: { token: string }) {
    const qc = useQueryClient();
    const [q, setQ] = useState("");
    const [discountFilter, setDiscountFilter] = useState("");
    const [statusFilter, setStatusFilter] = useState("");
    const [page, setPage] = useState(1);

    const { data: offers = [], isLoading } = useQuery<AdminOffer[]>({
        queryKey: ["admin", "offers"],
        queryFn: () => http<AdminOffer[]>("GET", endpoints.admin.offers, { headers: { Authorization: `Bearer ${token}` } }),
    });

    const toggleMutation = useMutation({
        mutationFn: (id: string) => http<{ id: string; adminDisabled: boolean }>("PATCH", endpoints.admin.toggleOffer(id), { headers: { Authorization: `Bearer ${token}` } }),
        onSuccess: () => { qc.invalidateQueries({ queryKey: ["admin", "offers"] }); qc.invalidateQueries({ queryKey: ["admin", "stats"] }); },
    });

    const discountOptions = useMemo(() => {
        const types = [...new Set(offers.map(o => o.discountType).filter(Boolean))];
        return types.sort().map(t => ({ value: t, label: t }));
    }, [offers]);

    const filtered = useMemo(() => {
        return offers.filter(o => {
            if (q && !o.title.toLowerCase().includes(q.toLowerCase()) && !o.slug.toLowerCase().includes(q.toLowerCase())) return false;
            if (discountFilter && o.discountType !== discountFilter) return false;
            if (statusFilter === "active" && !o.isActive) return false;
            if (statusFilter === "inactive" && o.isActive) return false;
            return true;
        });
    }, [offers, q, discountFilter, statusFilter]);

    const totalPages = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
    const paginated = filtered.slice((page - 1) * PAGE_SIZE, page * PAGE_SIZE);

    const hasFilters = q || discountFilter || statusFilter;

    return (
        <div className="space-y-6">
            <div>
                <h2 className="text-2xl font-black text-slate-900 dark:text-white mb-1">Ofertas</h2>
                <p className="text-slate-500 dark:text-slate-400 font-medium">
                    {filtered.length} de {offers.length} ofertas{hasFilters ? " (filtradas)" : " registradas"}
                </p>
            </div>
            {/* Filters */}
            <div className="flex flex-wrap gap-3 items-center bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4 shadow-sm">
                <SearchInput value={q} onChange={v => { setQ(v); setPage(1); }} placeholder="Buscar titulo o slug..." />
                <SelectFilter
                    value={discountFilter}
                    onChange={v => { setDiscountFilter(v); setPage(1); }}
                    options={discountOptions}
                    placeholder="Todos los tipos"
                />
                <SelectFilter
                    value={statusFilter}
                    onChange={v => { setStatusFilter(v); setPage(1); }}
                    options={[{ value: "active", label: "Activas" }, { value: "inactive", label: "Inactivas" }]}
                    placeholder="Todos los estados"
                />
                {hasFilters && (
                    <button
                        onClick={() => { setQ(""); setDiscountFilter(""); setStatusFilter(""); setPage(1); }}
                        className="flex items-center gap-1 px-3 py-2 text-xs font-black text-slate-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors"
                    >
                        <span className="material-symbols-outlined !text-sm">filter_list_off</span>
                        Limpiar
                    </button>
                )}
                <div className="ml-auto text-xs font-bold text-slate-400">
                    {paginated.length} mostradas · {PAGE_SIZE}/pagina
                </div>
            </div>
            <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden shadow-sm">
                <div className="overflow-x-auto">
                    <table className="w-full text-sm min-w-[900px]">
                        <thead>
                            <tr className="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                                <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Titulo</th>
                                <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Precio</th>
                                <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Puntos VAC</th>
                                <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Stock</th>
                                <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Tipo descuento</th>
                                <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Estado</th>
                                <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Fecha</th>
                                <th className="px-5 py-3 text-right font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {isLoading && [...Array(5)].map((_, i) => (
                                <tr key={i} className="border-b border-slate-50 dark:border-slate-800/50">
                                    <td colSpan={8} className="px-5 py-3"><div className="h-4 bg-slate-100 dark:bg-slate-800 rounded animate-pulse w-48" /></td>
                                </tr>
                            ))}
                            {paginated.map(offer => (
                                <tr key={offer.id} className={`border-b border-slate-50 dark:border-slate-800/50 transition-colors ${offer.adminDisabled ? "opacity-50 bg-red-50/30 dark:bg-red-900/10" : "hover:bg-slate-50/50 dark:hover:bg-slate-800/30"}`}>
                                    <td className="px-5 py-3 max-w-[200px]">
                                        <div>
                                            <p className={`font-semibold truncate ${offer.adminDisabled ? "line-through text-slate-400" : "text-slate-900 dark:text-white"}`} title={offer.title}>{offer.title}</p>
                                            <p className="text-[11px] font-mono text-slate-400 truncate">{offer.slug}</p>
                                        </div>
                                    </td>
                                    <td className="px-5 py-3 font-bold text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                        {offer.price} {offer.currency}
                                    </td>
                                    <td className="px-5 py-3">
                                        <span className="text-xs font-black text-primary bg-primary/10 px-2 py-0.5 rounded-lg">
                                            {offer.pointsCost} VAC
                                        </span>
                                    </td>
                                    <td className="px-5 py-3 text-slate-600 dark:text-slate-300 text-xs font-bold">
                                        {offer.quantity === -1 ? "∞" : offer.quantity}
                                    </td>
                                    <td className="px-5 py-3">
                                        <Badge text={offer.discountType} colorClass="bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400" />
                                    </td>
                                    <td className="px-5 py-3">
                                        <Badge
                                            text={offer.isActive ? "Activa" : "Inactiva"}
                                            colorClass={offer.isActive
                                                ? "bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400"
                                                : "bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400"}
                                        />
                                    </td>
                                    <td className="px-5 py-3 text-slate-500 dark:text-slate-400 text-xs whitespace-nowrap">
                                        {new Date(offer.createdAt).toLocaleDateString("es-ES", { day: "2-digit", month: "short", year: "numeric" })}
                                    </td>
                                    <td className="px-5 py-3">
                                        <div className="flex items-center gap-1 justify-end">
                                            {!offer.adminDisabled && (
                                                <a
                                                    href={`/offer/${offer.slug}`}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="p-1.5 rounded-lg text-slate-400 hover:text-primary hover:bg-primary/10 transition-colors"
                                                    title="Ver oferta"
                                                >
                                                    <span className="material-symbols-outlined !text-base">open_in_new</span>
                                                </a>
                                            )}
                                            <button
                                                onClick={() => toggleMutation.mutate(offer.id)}
                                                disabled={toggleMutation.isPending}
                                                title={offer.adminDisabled ? "Reactivar oferta" : "Desactivar oferta (admin)"}
                                                className={`p-1.5 rounded-lg transition-colors ${offer.adminDisabled ? "text-green-500 hover:bg-green-50 dark:hover:bg-green-900/20" : "text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20"}`}
                                            >
                                                <span className="material-symbols-outlined !text-base">{offer.adminDisabled ? "lock_open" : "block"}</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {!isLoading && filtered.length === 0 && (
                                <tr><td colSpan={8} className="px-5 py-10 text-center text-slate-400 font-bold">Sin resultados para los filtros aplicados</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
                <div className="px-5 py-3">
                    <Pagination page={page} totalPages={totalPages} onChange={setPage} />
                </div>
            </div>
        </div>
    );
}

// --- Section: Businesses -----------------------------------------------------

function BusinessesSection({ token }: { token: string }) {
    const qc = useQueryClient();
    const [q, setQ] = useState("");
    const [statusFilter, setStatusFilter] = useState("");
    const [page, setPage] = useState(1);

    const { data: businesses = [], isLoading } = useQuery<AdminBusiness[]>({
        queryKey: ["admin", "businesses"],
        queryFn: () => http<AdminBusiness[]>("GET", endpoints.admin.businesses, { headers: { Authorization: `Bearer ${token}` } }),
    });

    const toggleMutation = useMutation({
        mutationFn: (id: string) => http<{ userId: string; isActive: boolean }>("PATCH", endpoints.admin.toggleBusiness(id), { headers: { Authorization: `Bearer ${token}` } }),
        onSuccess: () => { qc.invalidateQueries({ queryKey: ["admin", "businesses"] }); qc.invalidateQueries({ queryKey: ["admin", "stats"] }); },
    });

    const filtered = useMemo(() => {
        return businesses.filter(b => {
            if (q && !b.name.toLowerCase().includes(q.toLowerCase()) && !b.slug.toLowerCase().includes(q.toLowerCase())) return false;
            if (statusFilter === "active" && !b.isActive) return false;
            if (statusFilter === "inactive" && b.isActive) return false;
            return true;
        });
    }, [businesses, q, statusFilter]);

    const totalPages = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
    const paginated = filtered.slice((page - 1) * PAGE_SIZE, page * PAGE_SIZE);

    const hasFilters = q || statusFilter;

    return (
        <div className="space-y-6">
            <div>
                <h2 className="text-2xl font-black text-slate-900 dark:text-white mb-1">Empresas</h2>
                <p className="text-slate-500 dark:text-slate-400 font-medium">
                    {filtered.length} de {businesses.length} empresas{hasFilters ? " (filtradas)" : " registradas"}
                </p>
            </div>
            {/* Filters */}
            <div className="flex flex-wrap gap-3 items-center bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4 shadow-sm">
                <SearchInput value={q} onChange={v => { setQ(v); setPage(1); }} placeholder="Buscar nombre o slug..." />
                <SelectFilter
                    value={statusFilter}
                    onChange={v => { setStatusFilter(v); setPage(1); }}
                    options={[{ value: "active", label: "Activas" }, { value: "inactive", label: "Inactivas" }]}
                    placeholder="Todos los estados"
                />
                {hasFilters && (
                    <button
                        onClick={() => { setQ(""); setStatusFilter(""); setPage(1); }}
                        className="flex items-center gap-1 px-3 py-2 text-xs font-black text-slate-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors"
                    >
                        <span className="material-symbols-outlined !text-sm">filter_list_off</span>
                        Limpiar
                    </button>
                )}
                <div className="ml-auto text-xs font-bold text-slate-400">
                    {paginated.length} mostradas · {PAGE_SIZE}/pagina
                </div>
            </div>
            <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden shadow-sm">
                <div className="overflow-x-auto">
                    <table className="w-full text-sm min-w-[700px]">
                        <thead>
                            <tr className="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                                <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Empresa</th>
                                <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Slug</th>
                                <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Coordenadas</th>
                                <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Estado</th>
                                <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Registro</th>
                                <th className="px-5 py-3 text-right font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {isLoading && [...Array(5)].map((_, i) => (
                                <tr key={i} className="border-b border-slate-50 dark:border-slate-800/50">
                                    <td colSpan={6} className="px-5 py-3"><div className="h-4 bg-slate-100 dark:bg-slate-800 rounded animate-pulse w-48" /></td>
                                </tr>
                            ))}
                            {paginated.map(biz => (
                                <tr key={biz.userId} className={`border-b border-slate-50 dark:border-slate-800/50 transition-colors ${!biz.isActive ? "opacity-50 bg-red-50/30 dark:bg-red-900/10" : "hover:bg-slate-50/50 dark:hover:bg-slate-800/30"}`}>
                                    <td className="px-5 py-3">
                                        <div className="flex items-center gap-3">
                                            {biz.profileIcon ? (
                                                <img src={biz.profileIcon} alt={biz.name} className="w-8 h-8 rounded-xl object-cover border border-slate-100 dark:border-slate-700" />
                                            ) : (
                                                <div className="w-8 h-8 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                                                    <span className="material-symbols-outlined !text-sm text-primary">business</span>
                                                </div>
                                            )}
                                            <span className="font-semibold text-slate-900 dark:text-white">{biz.name}</span>
                                        </div>
                                    </td>
                                    <td className="px-5 py-3 text-slate-500 dark:text-slate-400 text-xs font-mono">{biz.slug}</td>
                                    <td className="px-5 py-3 text-slate-500 dark:text-slate-400 text-xs font-bold">
                                        {biz.lat != null && biz.lng != null
                                            ? <span className="font-mono">{biz.lat.toFixed(4)}, {biz.lng.toFixed(4)}</span>
                                            : <span className="text-slate-300 dark:text-slate-600">Sin ubicacion</span>}
                                    </td>
                                    <td className="px-5 py-3">
                                        <Badge
                                            text={biz.isActive ? "Activa" : "Inactiva"}
                                            colorClass={biz.isActive
                                                ? "bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400"
                                                : "bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400"}
                                        />
                                    </td>
                                    <td className="px-5 py-3 text-slate-500 dark:text-slate-400 text-xs whitespace-nowrap">
                                        {new Date(biz.createdAt).toLocaleDateString("es-ES", { day: "2-digit", month: "short", year: "numeric" })}
                                    </td>
                                    <td className="px-5 py-3">
                                        <div className="flex items-center gap-1 justify-end">
                                            {biz.isActive && (
                                                <a
                                                    href={`/profile/${biz.slug}`}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="p-1.5 rounded-lg text-slate-400 hover:text-primary hover:bg-primary/10 transition-colors"
                                                    title="Ver perfil"
                                                >
                                                    <span className="material-symbols-outlined !text-base">open_in_new</span>
                                                </a>
                                            )}
                                            <button
                                                onClick={() => toggleMutation.mutate(biz.userId)}
                                                disabled={toggleMutation.isPending}
                                                title={biz.isActive ? "Desactivar empresa" : "Reactivar empresa"}
                                                className={`p-1.5 rounded-lg transition-colors ${!biz.isActive ? "text-green-500 hover:bg-green-50 dark:hover:bg-green-900/20" : "text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20"}`}
                                            >
                                                <span className="material-symbols-outlined !text-base">{biz.isActive ? "block" : "lock_open"}</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {!isLoading && filtered.length === 0 && (
                                <tr><td colSpan={6} className="px-5 py-10 text-center text-slate-400 font-bold">Sin resultados para los filtros aplicados</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
                <div className="px-5 py-3">
                    <Pagination page={page} totalPages={totalPages} onChange={setPage} />
                </div>
            </div>
        </div>
    );
}

// --- Section: Sports ----------------------------------------------------------

function SportsSection({ token }: { token: string }) {
    const qc = useQueryClient();
    const [newCode, setNewCode] = useState("");
    const [newName, setNewName] = useState("");
    const [editingId, setEditingId] = useState<string | null>(null);
    const [editName, setEditName] = useState("");
    const [q, setQ] = useState("");
    const [statusFilter, setStatusFilter] = useState("");

    const { data: sports = [], isLoading } = useQuery<AdminSport[]>({
        queryKey: ["admin", "sports"],
        queryFn: () => http<AdminSport[]>("GET", endpoints.admin.sports, { headers: { Authorization: `Bearer ${token}` } }),
    });

    const createMutation = useMutation({
        mutationFn: () => http<AdminSport>("POST", endpoints.admin.createSport, {
            headers: { Authorization: `Bearer ${token}` },
            body: { code: newCode.toUpperCase().trim(), name: newName.trim() },
        }),
        onSuccess: () => { setNewCode(""); setNewName(""); qc.invalidateQueries({ queryKey: ["admin", "sports"] }); qc.invalidateQueries({ queryKey: ["admin", "stats"] }); },
    });

    const toggleMutation = useMutation({
        mutationFn: (id: string) => http<{ isActive: boolean }>("PATCH", endpoints.admin.toggleSport(id), { headers: { Authorization: `Bearer ${token}` } }),
        onSuccess: () => qc.invalidateQueries({ queryKey: ["admin", "sports"] }),
    });

    const updateMutation = useMutation({
        mutationFn: ({ id, name }: { id: string; name: string }) =>
            http<{ id: string }>("PATCH", endpoints.admin.updateSport(id), {
                headers: { Authorization: `Bearer ${token}` },
                body: { name },
            }),
        onSuccess: () => { setEditingId(null); qc.invalidateQueries({ queryKey: ["admin", "sports"] }); },
    });

    const filtered = useMemo(() => {
        return sports.filter(s => {
            if (q && !s.name.toLowerCase().includes(q.toLowerCase()) && !s.code.toLowerCase().includes(q.toLowerCase())) return false;
            if (statusFilter === "active" && !s.isActive) return false;
            if (statusFilter === "inactive" && s.isActive) return false;
            return true;
        });
    }, [sports, q, statusFilter]);

    const hasFilters = q || statusFilter;

    return (
        <div className="space-y-6">
            <div>
                <h2 className="text-2xl font-black text-slate-900 dark:text-white mb-1">Deportes</h2>
                <p className="text-slate-500 dark:text-slate-400 font-medium">
                    {filtered.length} de {sports.length} deportes{hasFilters ? " (filtrados)" : " configurados"}
                </p>
            </div>
            {/* Add sport form */}
            <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 shadow-sm">
                <h3 className="font-black text-slate-700 dark:text-slate-300 text-sm uppercase tracking-wider mb-4">Anadir deporte</h3>
                <div className="flex items-end gap-3 flex-wrap">
                    <div>
                        <label className="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Codigo</label>
                        <input
                            value={newCode}
                            onChange={e => setNewCode(e.target.value.toUpperCase())}
                            placeholder="SKATE"
                            maxLength={8}
                            className="px-3 py-2 text-sm font-mono font-black bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/40 w-28"
                        />
                    </div>
                    <div>
                        <label className="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Nombre</label>
                        <input
                            value={newName}
                            onChange={e => setNewName(e.target.value)}
                            placeholder="Skateboarding"
                            className="px-3 py-2 text-sm font-medium bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/40 w-48"
                        />
                    </div>
                    <button
                        onClick={() => createMutation.mutate()}
                        disabled={!newCode.trim() || !newName.trim() || createMutation.isPending}
                        className="px-5 py-2 bg-primary text-white font-black rounded-xl hover:bg-blue-600 transition-colors disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2"
                    >
                        <span className="material-symbols-outlined !text-base">add</span>
                        Crear
                    </button>
                    {createMutation.isError && (
                        <p className="text-red-500 text-xs font-bold">Error: codigo ya existe o datos invalidos.</p>
                    )}
                </div>
            </div>
            {/* Filters */}
            <div className="flex flex-wrap gap-3 items-center bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4 shadow-sm">
                <SearchInput value={q} onChange={setQ} placeholder="Buscar codigo o nombre..." />
                <SelectFilter
                    value={statusFilter}
                    onChange={setStatusFilter}
                    options={[{ value: "active", label: "Activos" }, { value: "inactive", label: "Inactivos" }]}
                    placeholder="Todos los estados"
                />
                {hasFilters && (
                    <button
                        onClick={() => { setQ(""); setStatusFilter(""); }}
                        className="flex items-center gap-1 px-3 py-2 text-xs font-black text-slate-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors"
                    >
                        <span className="material-symbols-outlined !text-sm">filter_list_off</span>
                        Limpiar
                    </button>
                )}
            </div>
            <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden shadow-sm">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                            <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Codigo</th>
                            <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Nombre</th>
                            <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Estado</th>
                            <th className="text-left font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] px-5 py-3">Creado</th>
                            <th className="px-5 py-3 text-right font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {isLoading && [...Array(4)].map((_, i) => (
                            <tr key={i} className="border-b border-slate-50 dark:border-slate-800/50">
                                <td colSpan={5} className="px-5 py-3"><div className="h-4 bg-slate-100 dark:bg-slate-800 rounded animate-pulse w-32" /></td>
                            </tr>
                        ))}
                        {filtered.map(sport => (
                            <tr key={sport.id} className="border-b border-slate-50 dark:border-slate-800/50 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td className="px-5 py-3">
                                    <span className="font-mono font-black text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-lg text-xs">{sport.code}</span>
                                </td>
                                <td className="px-5 py-3">
                                    {editingId === sport.id ? (
                                        <div className="flex items-center gap-2">
                                            <input
                                                value={editName}
                                                onChange={e => setEditName(e.target.value)}
                                                className="px-2 py-1 text-sm bg-slate-50 dark:bg-slate-800 border border-primary/40 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/40"
                                                autoFocus
                                                onKeyDown={e => { if (e.key === "Enter") updateMutation.mutate({ id: sport.id, name: editName }); if (e.key === "Escape") setEditingId(null); }}
                                            />
                                            <button
                                                onClick={() => updateMutation.mutate({ id: sport.id, name: editName })}
                                                disabled={updateMutation.isPending}
                                                className="p-1 rounded-lg bg-primary text-white hover:bg-blue-600 transition-colors"
                                            >
                                                <span className="material-symbols-outlined !text-sm">check</span>
                                            </button>
                                            <button onClick={() => setEditingId(null)} className="p-1 rounded-lg text-slate-400 hover:text-slate-600 transition-colors">
                                                <span className="material-symbols-outlined !text-sm">close</span>
                                            </button>
                                        </div>
                                    ) : (
                                        <span className="font-semibold text-slate-900 dark:text-white">{sport.name}</span>
                                    )}
                                </td>
                                <td className="px-5 py-3">
                                    <Badge
                                        text={sport.isActive ? "Activo" : "Inactivo"}
                                        colorClass={sport.isActive
                                            ? "bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400"
                                            : "bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400"}
                                    />
                                </td>
                                <td className="px-5 py-3 text-slate-500 dark:text-slate-400 text-xs whitespace-nowrap">
                                    {new Date(sport.createdAt).toLocaleDateString("es-ES", { day: "2-digit", month: "short", year: "numeric" })}
                                </td>
                                <td className="px-5 py-3">
                                    <div className="flex items-center gap-1 justify-end">
                                        {editingId !== sport.id && (
                                            <button
                                                onClick={() => { setEditingId(sport.id); setEditName(sport.name); }}
                                                className="p-1.5 rounded-lg text-slate-400 hover:text-primary hover:bg-primary/10 transition-colors"
                                                title="Editar nombre"
                                            >
                                                <span className="material-symbols-outlined !text-base">edit</span>
                                            </button>
                                        )}
                                        <button
                                            onClick={() => toggleMutation.mutate(sport.id)}
                                            disabled={toggleMutation.isPending}
                                            title={sport.isActive ? "Desactivar" : "Activar"}
                                            className="p-1.5 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors"
                                        >
                                            <span className="material-symbols-outlined !text-base">{sport.isActive ? "toggle_on" : "toggle_off"}</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        ))}
                        {!isLoading && filtered.length === 0 && (
                            <tr><td colSpan={5} className="px-5 py-10 text-center text-slate-400 font-bold">Sin deportes</td></tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
// --- Nav items ----------------------------------------------------------------

const NAV_ITEMS: { tab: Tab; icon: string; label: string }[] = [
    { tab: "dashboard", icon: "dashboard", label: "Dashboard" },
    { tab: "users", icon: "group", label: "Usuarios" },
    { tab: "routes", icon: "route", label: "Rutas" },
    { tab: "offers", icon: "storefront", label: "Ofertas" },
    { tab: "businesses", icon: "business", label: "Empresas" },
    { tab: "sports", icon: "sports", label: "Deportes" },
];

// --- Main AdminPage -----------------------------------------------------------

export function AdminPage() {
    const { user, token, isAuthenticated } = useAuthStore();
    const navigate = useNavigate();
    const [activeTab, setActiveTab] = useState<Tab>("dashboard");

    const isAdmin = user?.role === "ROLE_ADMIN";

    const { data: stats } = useQuery<AdminStats>({
        queryKey: ["admin", "stats"],
        queryFn: () => http<AdminStats>("GET", endpoints.admin.stats, { headers: { Authorization: `Bearer ${token}` } }),
        enabled: isAdmin && !!token,
    });

    if (!isAuthenticated || !isAdmin) {
        return (
            <div className="min-h-screen bg-white dark:bg-background-dark flex items-center justify-center">
                <div className="text-center space-y-4">
                    <div className="w-20 h-20 bg-red-100 dark:bg-red-900/30 rounded-3xl flex items-center justify-center mx-auto">
                        <span className="material-symbols-outlined !text-4xl text-red-500">lock</span>
                    </div>
                    <h1 className="text-2xl font-black text-slate-900 dark:text-white">Acceso denegado</h1>
                    <p className="text-slate-500 dark:text-slate-400 font-medium">Necesitas permisos de administrador.</p>
                    <button onClick={() => navigate("/")} className="px-6 py-2.5 bg-primary text-white font-black rounded-xl hover:bg-blue-600 transition-colors">
                        Volver al inicio
                    </button>
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-slate-50 dark:bg-[#080c14] flex font-display antialiased">
            {/* Sidebar */}
            <aside className="w-60 shrink-0 bg-white dark:bg-slate-900 border-r border-slate-100 dark:border-slate-800 flex flex-col">
                <div className="p-5 border-b border-slate-100 dark:border-slate-800">
                    <button onClick={() => navigate("/")} className="flex items-center gap-2 hover:opacity-80 transition-opacity">
                        <span className="material-symbols-outlined text-primary text-2xl font-black">explore</span>
                        <span className="font-black text-slate-900 dark:text-white tracking-tight">Vall Activa</span>
                    </button>
                    <div className="mt-3 px-2 py-1 bg-red-50 dark:bg-red-900/20 rounded-lg inline-flex items-center gap-1.5">
                        <span className="material-symbols-outlined !text-xs text-red-500">shield</span>
                        <span className="text-[10px] font-black text-red-600 dark:text-red-400 uppercase tracking-widest">Admin</span>
                    </div>
                </div>
                <nav className="flex-1 p-3 space-y-0.5">
                    {NAV_ITEMS.map(item => (
                        <button
                            key={item.tab}
                            onClick={() => setActiveTab(item.tab)}
                            className={`w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold transition-all ${activeTab === item.tab
                                ? "bg-primary text-white shadow-sm"
                                : "text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white"
                                }`}
                        >
                            <span className="material-symbols-outlined !text-lg">{item.icon}</span>
                            {item.label}
                            {item.tab === "dashboard" && stats && (
                                <span className="ml-auto text-[10px] font-black opacity-70">
                                    {stats.users.total}
                                </span>
                            )}
                        </button>
                    ))}
                </nav>
                <div className="p-4 border-t border-slate-100 dark:border-slate-800">
                    <div className="flex items-center gap-3 px-2">
                        <div className="w-7 h-7 rounded-full bg-primary/10 flex items-center justify-center">
                            <span className="material-symbols-outlined !text-sm text-primary">person</span>
                        </div>
                        <div className="flex-1 min-w-0">
                            <p className="text-xs font-black text-slate-900 dark:text-white truncate">{user?.name ?? user?.email}</p>
                            <p className="text-[10px] text-slate-400 font-bold">Administrador</p>
                        </div>
                    </div>
                </div>
            </aside>

            {/* Main content */}
            <main className="flex-1 overflow-y-auto">
                <div className="max-w-6xl mx-auto p-8">
                    {activeTab === "dashboard" && stats && <DashboardSection stats={stats} />}
                    {activeTab === "dashboard" && !stats && (
                        <div className="space-y-4">
                            <div className="h-8 bg-slate-200 dark:bg-slate-800 rounded-xl animate-pulse w-48" />
                            <div className="grid grid-cols-2 lg:grid-cols-5 gap-4">
                                {[...Array(5)].map((_, i) => (
                                    <div key={i} className="h-24 bg-white dark:bg-slate-900 rounded-2xl animate-pulse border border-slate-100 dark:border-slate-800" />
                                ))}
                            </div>
                        </div>
                    )}
                    {activeTab === "users" && <UsersSection token={token!} />}
                    {activeTab === "routes" && <RoutesSection token={token!} />}
                    {activeTab === "offers" && <OffersSection token={token!} />}
                    {activeTab === "businesses" && <BusinessesSection token={token!} />}
                    {activeTab === "sports" && <SportsSection token={token!} />}
                </div>
            </main>
        </div>
    );
}
