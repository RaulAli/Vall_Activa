import { create } from "zustand";
import { persist } from "zustand/middleware";
import type { AuthUser } from "../features/auth/domain/types";

interface AuthState {
    token: string | null;
    user: AuthUser | null;
    isAuthenticated: boolean;

    setAuth: (token: string, user: AuthUser) => void;
    setUser: (user: AuthUser) => void;
    clearAuth: () => void;
}

export const useAuthStore = create<AuthState>()(
    persist(
        (set) => ({
            token: null,
            user: null,
            isAuthenticated: false,

            setAuth: (token, user) =>
                set({ token, user, isAuthenticated: true }),

            setUser: (user) => set({ user }),

            clearAuth: () =>
                set({ token: null, user: null, isAuthenticated: false }),
        }),
        {
            name: "vamo-auth",
            partialize: (state) => ({
                token: state.token,
                user: state.user,
                isAuthenticated: state.isAuthenticated,
            }),
        }
    )
);
