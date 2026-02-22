import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { useAuthStore } from "../store/authStore";
import { changePassword } from "../features/user/api/userApi";
import { HttpError } from "../shared/api/http";

export function SettingsPage() {
    const navigate = useNavigate();
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
