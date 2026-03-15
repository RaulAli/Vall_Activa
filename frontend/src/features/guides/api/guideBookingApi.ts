import { http } from "../../../shared/api/http";
import { endpoints } from "../../../shared/api/endpoints";

export interface RouteBookingSlot {
    startsAt: string;
    date: string;
    time: string;
    day: "MONDAY" | "TUESDAY" | "WEDNESDAY" | "THURSDAY" | "FRIDAY" | "SATURDAY" | "SUNDAY";
    isAvailable: boolean;
    bookingStatus: "OCCUPIED" | null;
}

export interface RouteBookingSlotsResponse {
    routeId: string;
    timezone: string;
    slotMinutes: number;
    slots: RouteBookingSlot[];
}

export interface CreateGuideBookingPayload {
    routeId: string;
    startsAt: string;
    notes?: string;
}

export interface GuideBookingCreated {
    id: string;
    routeId: string;
    guideUserId: string;
    athleteUserId: string;
    startsAt: string;
    status: string;
    notes: string | null;
    createdAt: string;
}

export interface GuideDashboardBookingItem {
    id: string;
    status: "REQUESTED" | "CONFIRMED" | "REJECTED" | "CANCELLED";
    notes: string | null;
    scheduledFor: string;
    createdAt: string;
    routeId: string | null;
    routeSlug: string | null;
    routeTitle: string | null;
    athleteUserId: string | null;
    athleteName: string | null;
    athleteSlug: string | null;
    athleteAvatar: string | null;
}

export interface AthleteDashboardBookingItem {
    id: string;
    status: "REQUESTED" | "CONFIRMED" | "REJECTED" | "CANCELLED";
    notes: string | null;
    scheduledFor: string;
    createdAt: string;
    routeId: string | null;
    routeSlug: string | null;
    routeTitle: string | null;
    guideUserId: string | null;
    guideName: string | null;
    guideSlug: string | null;
    guideAvatar: string | null;
    guideIsVerified: boolean | null;
}

export interface UpdateGuideBookingStatusPayload {
    status: "CONFIRMED" | "REJECTED";
}

export interface UpdateGuideBookingStatusResponse {
    id: string;
    status: "REQUESTED" | "CONFIRMED" | "REJECTED" | "CANCELLED";
}

export async function getRouteBookingSlots(slug: string, days = 14): Promise<RouteBookingSlotsResponse> {
    return http<RouteBookingSlotsResponse>("GET", endpoints.routes.bookingSlots(slug), {
        query: { days },
    });
}

export async function createGuideBooking(token: string, payload: CreateGuideBookingPayload): Promise<GuideBookingCreated> {
    return http<GuideBookingCreated>("POST", endpoints.guide.bookings, {
        headers: { Authorization: `Bearer ${token}` },
        body: payload,
    });
}

export async function getMyGuideBookings(token: string): Promise<GuideDashboardBookingItem[]> {
    return http<GuideDashboardBookingItem[]>("GET", endpoints.guide.myBookings, {
        headers: { Authorization: `Bearer ${token}` },
    });
}

export async function updateMyGuideBookingStatus(
    token: string,
    bookingId: string,
    payload: UpdateGuideBookingStatusPayload,
): Promise<UpdateGuideBookingStatusResponse> {
    return http<UpdateGuideBookingStatusResponse>("PATCH", endpoints.guide.updateBooking(bookingId), {
        headers: { Authorization: `Bearer ${token}` },
        body: payload,
    });
}

export async function getMyAthleteBookings(token: string): Promise<AthleteDashboardBookingItem[]> {
    return http<AthleteDashboardBookingItem[]>("GET", endpoints.athlete.myBookings, {
        headers: { Authorization: `Bearer ${token}` },
    });
}
