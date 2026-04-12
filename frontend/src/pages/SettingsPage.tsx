import { useState, useEffect } from "react";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useNavigate } from "react-router-dom";
import { useAuthStore } from "../store/authStore";
import { changePassword } from "../features/user/api/userApi";
import { closeMyIncident, createIncident, listIncidentCategories, listMyIncidents } from "../features/incidents/api/incidentsApi";
import { HttpError } from "../shared/api/http";

export function SettingsPage() {
    const navigate = useNavigate();
    const qc = useQueryClient();
    const { isAuthenticated, user, token, clearAuth } = useAuthStore();

    useEffect(() => {
        if (!isAuthenticated) navigate("/auth", { replace: true });
    }, [isAuthenticated, navigate]);

    // Change password form
    const [currentPassword, setCurrentPassword] = useState("");
    const [newPassword, setNewPassword] = useState("");
    const [confirmPassword, setConfirmPassword] = useState("");
    const [showCurrent, setShowCurrent] = useState(false);
    const [showNew, setShowNew] = useState(false);
    const [pwLoading, setPwLoading] = useState(false);
    const [pwError, setPwError] = useState<string | null>(null);
    const [pwSuccess, setPwSuccess] = useState(false);

    // Incident form
    const [incidentCategory, setIncidentCategory] = useState("TECHNICAL");
    const [incidentSubject, setIncidentSubject] = useState("");
    const [incidentMessage, setIncidentMessage] = useState("");
    const [incidentLoading, setIncidentLoading] = useState(false);
    const [incidentError, setIncidentError] = useState<string | null>(null);
    const [incidentSuccess, setIncidentSuccess] = useState(false);

    const { data: incidentCategories = [] } = useQuery({
        queryKey: ["incident-categories"],
        queryFn: () => listIncidentCategories(token!),
        enabled: !!token,
    });

    const { data: myIncidents = [], isLoading: isLoadingMyIncidents } = useQuery({
        queryKey: ["my-incidents"],
        queryFn: () => listMyIncidents(token!),
        enabled: !!token,
    });

    const closeIncidentMutation = useMutation({
        mutationFn: (id: string) => closeMyIncident(token!, id),
        onSuccess: () => {
            qc.invalidateQueries({ queryKey: ["my-incidents"] });
            qc.invalidateQueries({ queryKey: ["admin", "incidents"] });
        },
    });

    useEffect(() => {
        if (incidentCategories.length === 0) return;
        const exists = incidentCategories.some((c) => c.code === incidentCategory);
        if (!exists) {
            setIncidentCategory(incidentCategories[0].code);
        }
    }, [incidentCategories, incidentCategory]);

    if (!user || !isAuthenticated) return null;

    const handleChangePassword = async (e: React.FormEvent) => {
        e.preventDefault();
        setPwError(null);
        setPwSuccess(false);

        if (newPassword.length < 8) {
            setPwError("New password must be at least 8 characters.");
            return;
        }
        if (newPassword !== confirmPassword) {
            setPwError("Passwords do not match.");
            return;
        }

        setPwLoading(true);
        try {
            await changePassword(token!, { currentPassword, newPassword });
            setPwSuccess(true);
            setCurrentPassword("");
            setNewPassword("");
            setConfirmPassword("");
            setTimeout(() => setPwSuccess(false), 4000);
        } catch (err) {
            if (err instanceof HttpError) {
                const code = (err.body as Record<string, unknown>).error as string;
                if (code === "invalid_current_password") {
                    setPwError("Current password is incorrect.");
                } else if (code === "new_password_too_short") {
                    setPwError("New password must be at least 8 characters.");
                } else {
                    setPwError("Failed to change password. Please try again.");
                }
            } else {
                setPwError("Failed to change password. Please try again.");
            }
        } finally {
            setPwLoading(false);
        }
    };

    const inputClass = "w-full rounded-xl border border-slate-200 dark:border-slate-700 h-12 px-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all bg-white dark:bg-[#1a2333]";

    const handleCreateIncident = async (e: React.FormEvent) => {
        e.preventDefault();
        setIncidentError(null);
        setIncidentSuccess(false);

        const subject = incidentSubject.trim();
        const message = incidentMessage.trim();

        if (subject.length < 4) {
            setIncidentError("Subject must have at least 4 characters.");
            return;
        }
        if (message.length < 10) {
            setIncidentError("Message must have at least 10 characters.");
            return;
        }

        setIncidentLoading(true);
        try {
            await createIncident(token!, {
                category: incidentCategory,
                subject,
                message,
            });

            setIncidentSubject("");
            setIncidentMessage("");
            setIncidentSuccess(true);
            qc.invalidateQueries({ queryKey: ["my-incidents"] });
            qc.invalidateQueries({ queryKey: ["admin", "incidents"] });
            setTimeout(() => setIncidentSuccess(false), 4500);
        } catch (err) {
            if (err instanceof HttpError) {
                const message = (err.body as Record<string, unknown>).message;
                setIncidentError(typeof message === "string" ? message : "Failed to submit incident.");
            } else {
                setIncidentError("Failed to submit incident.");
            }
        } finally {
            setIncidentLoading(false);
        }
    };

    return (
        <div className="min-h-screen bg-slate-50 dark:bg-background-dark">
            {/* Header */}
            <div className="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
                <div className="max-w-3xl mx-auto px-4 sm:px-6 py-4 flex items-center gap-3">
                    <button
                        onClick={() => navigate(-1)}
                        className="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-slate-500 dark:text-slate-400"
                    >
                        <span className="material-symbols-outlined">arrow_back</span>
                    </button>
                    <h1 className="text-lg font-black text-slate-900 dark:text-white">Settings</h1>
                </div>
            </div>

            <div className="max-w-3xl mx-auto px-4 sm:px-6 py-8 space-y-6">

                {/* Change Password */}
                <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div className="p-6 border-b border-slate-100 dark:border-slate-800">
                        <div className="flex items-center gap-3">
                            <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                                <span className="material-symbols-outlined">lock</span>
                            </div>
                            <div>
                                <h2 className="text-base font-black text-slate-900 dark:text-white">Change Password</h2>
                                <p className="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Update your account password</p>
                            </div>
                        </div>
                    </div>

                    <form onSubmit={handleChangePassword} className="p-6 space-y-4">
                        {pwSuccess && (
                            <div className="flex items-center gap-2 px-4 py-3 bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-800 rounded-xl text-sm text-green-700 dark:text-green-400 font-semibold">
                                <span className="material-symbols-outlined !text-base">check_circle</span>
                                Password changed successfully.
                            </div>
                        )}
                        {pwError && (
                            <div className="flex items-center gap-2 px-4 py-3 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-600 dark:text-red-400 font-semibold">
                                <span className="material-symbols-outlined !text-base">error</span>
                                {pwError}
                            </div>
                        )}

                        <div>
                            <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                Current Password
                            </label>
                            <div className="relative">
                                <input
                                    type={showCurrent ? "text" : "password"}
                                    value={currentPassword}
                                    onChange={(e) => setCurrentPassword(e.target.value)}
                                    required
                                    className={`${inputClass} pr-12`}
                                    placeholder="Your current password"
                                    autoComplete="current-password"
                                />
                                <button
                                    type="button"
                                    onClick={() => setShowCurrent((v) => !v)}
                                    className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors"
                                >
                                    <span className="material-symbols-outlined !text-xl">
                                        {showCurrent ? "visibility_off" : "visibility"}
                                    </span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                New Password
                            </label>
                            <div className="relative">
                                <input
                                    type={showNew ? "text" : "password"}
                                    value={newPassword}
                                    onChange={(e) => setNewPassword(e.target.value)}
                                    required
                                    className={`${inputClass} pr-12`}
                                    placeholder="At least 8 characters"
                                    autoComplete="new-password"
                                />
                                <button
                                    type="button"
                                    onClick={() => setShowNew((v) => !v)}
                                    className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors"
                                >
                                    <span className="material-symbols-outlined !text-xl">
                                        {showNew ? "visibility_off" : "visibility"}
                                    </span>
                                </button>
                            </div>
                            {newPassword.length > 0 && (
                                <div className="mt-2 flex gap-1">
                                    {[...Array(4)].map((_, i) => {
                                        const strength = Math.min(4, Math.floor(newPassword.length / 3));
                                        return (
                                            <div
                                                key={i}
                                                className={`h-1 flex-1 rounded-full transition-colors ${i < strength
                                                    ? strength <= 1 ? "bg-red-400" : strength <= 2 ? "bg-yellow-400" : strength <= 3 ? "bg-blue-400" : "bg-green-400"
                                                    : "bg-slate-200 dark:bg-slate-700"
                                                    }`}
                                            />
                                        );
                                    })}
                                </div>
                            )}
                        </div>

                        <div>
                            <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                Confirm New Password
                            </label>
                            <input
                                type="password"
                                value={confirmPassword}
                                onChange={(e) => setConfirmPassword(e.target.value)}
                                required
                                className={`${inputClass} ${confirmPassword && confirmPassword !== newPassword
                                    ? "border-red-400 focus:ring-red-300"
                                    : ""
                                    }`}
                                placeholder="Repeat new password"
                                autoComplete="new-password"
                            />
                            {confirmPassword && confirmPassword !== newPassword && (
                                <p className="mt-1.5 text-xs text-red-500 font-semibold">Passwords do not match.</p>
                            )}
                        </div>

                        <button
                            type="submit"
                            disabled={pwLoading || !currentPassword || !newPassword || !confirmPassword}
                            className="w-full h-12 rounded-xl bg-primary text-white text-sm font-bold hover:bg-primary/90 transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 mt-2"
                        >
                            {pwLoading ? (
                                <span className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                            ) : (
                                <>
                                    <span className="material-symbols-outlined !text-base">lock_reset</span>
                                    Update Password
                                </>
                            )}
                        </button>
                    </form>
                </div>

                {/* Incidents */}
                <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div className="p-6 border-b border-slate-100 dark:border-slate-800">
                        <div className="flex items-center gap-3">
                            <div className="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400">
                                <span className="material-symbols-outlined">report_problem</span>
                            </div>
                            <div>
                                <h2 className="text-base font-black text-slate-900 dark:text-white">Report Incident</h2>
                                <p className="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Send a technical or account issue to the admin team</p>
                            </div>
                        </div>
                    </div>

                    <form onSubmit={handleCreateIncident} className="p-6 space-y-4">
                        {incidentSuccess && (
                            <div className="flex items-center gap-2 px-4 py-3 bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-800 rounded-xl text-sm text-green-700 dark:text-green-400 font-semibold">
                                <span className="material-symbols-outlined !text-base">check_circle</span>
                                Incident sent successfully. Our admins will review it soon.
                            </div>
                        )}
                        {incidentError && (
                            <div className="flex items-center gap-2 px-4 py-3 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-600 dark:text-red-400 font-semibold">
                                <span className="material-symbols-outlined !text-base">error</span>
                                {incidentError}
                            </div>
                        )}

                        <div>
                            <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                Category
                            </label>
                            <select
                                value={incidentCategory}
                                onChange={(e) => setIncidentCategory(e.target.value)}
                                className={inputClass}
                            >
                                {incidentCategories.length === 0 && (
                                    <option value="">No categories available</option>
                                )}
                                {incidentCategories.map((category) => (
                                    <option key={category.code} value={category.code}>{category.name}</option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                Subject
                            </label>
                            <input
                                value={incidentSubject}
                                onChange={(e) => setIncidentSubject(e.target.value)}
                                maxLength={120}
                                required
                                className={inputClass}
                                placeholder="Short summary of the issue"
                            />
                        </div>

                        <div>
                            <label className="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                Message
                            </label>
                            <textarea
                                value={incidentMessage}
                                onChange={(e) => setIncidentMessage(e.target.value)}
                                maxLength={2000}
                                required
                                rows={5}
                                className="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all bg-white dark:bg-[#1a2333] resize-y"
                                placeholder="Describe what happened, where and when, and how we can reproduce it"
                            />
                        </div>

                        <button
                            type="submit"
                            disabled={incidentLoading || incidentCategories.length === 0 || !incidentSubject.trim() || !incidentMessage.trim()}
                            className="w-full h-12 rounded-xl bg-amber-500 text-white text-sm font-bold hover:bg-amber-600 transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                        >
                            {incidentLoading ? (
                                <span className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                            ) : (
                                <>
                                    <span className="material-symbols-outlined !text-base">send</span>
                                    Submit Incident
                                </>
                            )}
                        </button>
                    </form>
                </div>

                {/* My incidents history */}
                <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div className="p-6 border-b border-slate-100 dark:border-slate-800">
                        <div className="flex items-center gap-3">
                            <div className="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                <span className="material-symbols-outlined">history</span>
                            </div>
                            <div>
                                <h2 className="text-base font-black text-slate-900 dark:text-white">My Incident History</h2>
                                <p className="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Track all incidents you created and close them when solved</p>
                            </div>
                        </div>
                    </div>
                    <div className="p-6">
                        {isLoadingMyIncidents && (
                            <div className="space-y-2">
                                {[...Array(3)].map((_, i) => (
                                    <div key={i} className="h-10 rounded-lg bg-slate-100 dark:bg-slate-800 animate-pulse" />
                                ))}
                            </div>
                        )}

                        {!isLoadingMyIncidents && myIncidents.length === 0 && (
                            <p className="text-sm font-medium text-slate-500 dark:text-slate-400">You have not created incidents yet.</p>
                        )}

                        {!isLoadingMyIncidents && myIncidents.length > 0 && (
                            <div className="space-y-3">
                                {myIncidents.map((incident) => (
                                    <div key={incident.id} className="rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                                        <div className="flex items-start justify-between gap-3">
                                            <div>
                                                <p className="text-sm font-black text-slate-900 dark:text-white">{incident.subject}</p>
                                                <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{incident.category} · {new Date(incident.createdAt).toLocaleDateString("es-ES", { day: "2-digit", month: "short", year: "numeric" })}</p>
                                            </div>
                                            <span className={`px-2 py-0.5 rounded-full text-[11px] font-black uppercase tracking-wider ${incident.status === "RESOLVED"
                                                ? "bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400"
                                                : incident.status === "REVIEWING"
                                                    ? "bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400"
                                                    : "bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400"}`}>
                                                {incident.status}
                                            </span>
                                        </div>
                                        <p className="text-sm text-slate-600 dark:text-slate-300 mt-2">{incident.message}</p>
                                        <div className="flex justify-end mt-3">
                                            {incident.status !== "RESOLVED" && (
                                                <button
                                                    onClick={() => closeIncidentMutation.mutate(incident.id)}
                                                    disabled={closeIncidentMutation.isPending}
                                                    className="px-3 py-1.5 rounded-lg text-xs font-black bg-emerald-500 text-white hover:bg-emerald-600 disabled:opacity-50 transition-colors"
                                                >
                                                    Cerrar incidencia
                                                </button>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>

                {/* Danger zone */}
                <div className="bg-white dark:bg-slate-900 rounded-2xl border border-red-200 dark:border-red-900/50 overflow-hidden">
                    <div className="p-6 border-b border-red-100 dark:border-red-900/30">
                        <div className="flex items-center gap-3">
                            <div className="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-red-500">
                                <span className="material-symbols-outlined">warning</span>
                            </div>
                            <div>
                                <h2 className="text-base font-black text-slate-900 dark:text-white">Danger Zone</h2>
                                <p className="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Irreversible account actions</p>
                            </div>
                        </div>
                    </div>
                    <div className="p-6">
                        <div className="flex items-center justify-between gap-4">
                            <div>
                                <p className="text-sm font-bold text-slate-900 dark:text-white">Sign out from all devices</p>
                                <p className="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Invalidates all active sessions</p>
                            </div>
                            <button
                                onClick={() => { clearAuth(); navigate("/"); }}
                                className="shrink-0 flex items-center gap-1.5 px-4 py-2 rounded-xl border border-red-300 dark:border-red-800 text-sm font-bold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20 transition-all"
                            >
                                <span className="material-symbols-outlined !text-base">logout</span>
                                Sign out
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    );
}
