import { http } from "../../../shared/api/http";
import { endpoints } from "../../../shared/api/endpoints";
import type {
    LoginRequest,
    RegisterRequest,
    AuthResponse,
    AuthUser,
} from "../domain/types";

export async function login(data: LoginRequest): Promise<AuthResponse> {
    return http<AuthResponse>("POST", endpoints.auth.login, { body: data, withCredentials: true });
}

export async function register(data: RegisterRequest): Promise<void> {
    await http<{ id: string }>("POST", endpoints.auth.register, { body: data });
}

export async function registerAndLogin(data: RegisterRequest): Promise<AuthResponse> {
    await register(data);
    return login({ email: data.email, password: data.password });
}

export async function logout(token: string): Promise<void> {
    await http<void>("POST", endpoints.auth.logout, {
        headers: { Authorization: `Bearer ${token}` },
        withCredentials: true,
    });
}

export async function getMe(token: string): Promise<AuthUser> {
    return http<AuthUser>("GET", endpoints.auth.me, {
        headers: { Authorization: `Bearer ${token}` },
    });
}

export async function refreshToken(): Promise<AuthResponse> {
    return http<AuthResponse>("POST", endpoints.auth.refresh, { withCredentials: true });
}

