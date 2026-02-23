import { useQuery } from "@tanstack/react-query";
import { useAuthStore } from "../../../store/authStore";
import { getMyProfile } from "../api/businessProfileApi";

export function useMyProfileQuery() {
    const { token, user } = useAuthStore();
    const isB = user?.role === "ROLE_BUSINESS";

    return useQuery({
        queryKey: ["business", "profile"],
        queryFn: () => getMyProfile(token!),
        enabled: !!token && isB,
        staleTime: 60_000,
    });
}
