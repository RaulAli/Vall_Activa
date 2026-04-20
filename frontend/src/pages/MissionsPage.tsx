import { Header } from "../widgets/layout/Header";
import { AthletePointsCard } from "../features/points/ui/AthletePointsCard";
import { useAuthStore } from "../store/authStore";
import { useEffect } from "react";
import { useNavigate } from "react-router-dom";

export function MissionsPage() {
    const navigate = useNavigate();
    const { isAuthenticated, user } = useAuthStore();

    useEffect(() => {
        if (!isAuthenticated) {
            navigate("/auth", { replace: true });
            return;
        }

        if (user?.role !== "ROLE_ATHLETE") {
            navigate("/", { replace: true });
        }
    }, [isAuthenticated, navigate, user]);

    if (!isAuthenticated || user?.role !== "ROLE_ATHLETE") return null;

    return (
        <>
            <Header />
            <div className="min-h-screen bg-slate-50 dark:bg-background-dark">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 py-8 space-y-6">
                    <div>
                        <h1 className="text-2xl font-black text-slate-900 dark:text-white">Misiones diarias</h1>
                        <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
                            Completa misiones para ganar puntos extra y revisa tu progreso del dia.
                        </p>
                    </div>
                    <AthletePointsCard />
                </div>
            </div>
        </>
    );
}
