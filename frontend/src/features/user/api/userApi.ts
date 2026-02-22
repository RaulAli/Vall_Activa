import { http } from "../../../shared/api/http";
import { endpoints } from "../../../shared/api/endpoints";
import type { UpdateMeRequest, ChangePasswordRequest } from "../domain/types";
import type { AuthUser } from "../../auth/domain/types";

export async function getMe(token: string): Promise<AuthUser> {
    return http<AuthUser>("GET", endpoints.user.me, {
        headers: { Authorization: `Bearer ${token}` },
    });
}

export async function updateMe(token: string, data: UpdateMeRequest): Promise<void> {
    await http<void>("PATCH", endpoints.user.me, {
        headers: { Authorization: `Bearer ${token}` },
        body: data,
    });
}

export async function changePassword(token: string, data: ChangePasswordRequest): Promise<void> {
    await http<void>("PATCH", endpoints.user.password, {
        headers: { Authorization: `Bearer ${token}` },
        body: data,
    });
}
