import { useQuery } from "@tanstack/react-query";
import { getAllUsersAchievements } from "../services/admin";

export function useAdminUsersAchievements(enabled = false) {
    const query = useQuery({
        queryKey: ["adminUsersAchievements"],
        queryFn: getAllUsersAchievements,
        enabled, // only fetch when logged in
        refetchInterval: 10000, // optional: auto refresh every 10s
        select: (res) => res?.data ?? [], // flatten axios response
    });

    return {
        users: query.data?.data || [],
        isLoading: query.isLoading,
        isError: query.isError,
        refetch: query.refetch,
    };
}
