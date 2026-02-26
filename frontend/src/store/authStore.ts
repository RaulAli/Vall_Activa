import { create } from "zustand";
import { persist } from "zustand/middleware";
import type { AuthUser } from "../features/auth/domain/types";

interface AuthState {
    token: string | null;
    user: AuthUser | null;
    isAuthenticated: boolean;
    isInitializing: boolean;

    setAuth: (token: string, user: AuthUser) => void;
    setUser: (user: AuthUser) => void;
    clearAuth: () => void;
    setInitializing: (value: boolean) => void;
}

export const useAuthStore = create<AuthState>()(
    persist(
        (set) => ({
            token: null,
            user: null,
            isAuthenticated: false,
            isInitializing: true,

            setAuth: (token, user) =>
                set({ token, user, isAuthenticated: true }),

            setUser: (user) => set({ user }),

            clearAuth: () =>
                set({ token: null, user: null, isAuthenticated: false }),

            setInitializing: (value) => set({ isInitializing: value }),
        }),
        {
            name: "vamo-auth",
            // token stays in memory; only user profile is persisted
            partialize: (state) => ({
                user: state.user,
            }),
        }
    )
);
