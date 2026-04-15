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

export async function createVipCheckout(token: string, plan: "MONTHLY" | "YEARLY", returnOrigin: string): Promise<{ checkoutUrl: string; sessionId: string }> {
    return http<{ checkoutUrl: string; sessionId: string }>("POST", endpoints.athlete.vipCheckout, {
        headers: { Authorization: `Bearer ${token}` },
        body: { plan, returnOrigin },
    });
}

export async function confirmVipPayment(token: string, sessionId: string): Promise<{ vipPlan: "MONTHLY" | "YEARLY"; paidAt: string | null }> {
    return http<{ vipPlan: "MONTHLY" | "YEARLY"; paidAt: string | null }>("POST", endpoints.athlete.vipConfirmPayment, {
        headers: { Authorization: `Bearer ${token}` },
        body: { sessionId },
    });
}

export async function updateVipRenewal(token: string, cancelAtPeriodEnd: boolean): Promise<void> {
    await http<void>("PATCH", endpoints.athlete.vipRenewal, {
        headers: { Authorization: `Bearer ${token}` },
        body: { cancelAtPeriodEnd },
    });
}

export async function changePassword(token: string, data: ChangePasswordRequest): Promise<void> {
    await http<void>("PATCH", endpoints.user.password, {
        headers: { Authorization: `Bearer ${token}` },
        body: data,
    });
}
