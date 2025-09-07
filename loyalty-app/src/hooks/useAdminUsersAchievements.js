import { useQuery } from "@tanstack/react-query";
import { useAchievementsApi } from "./useAchievementsApi";

/**
 * Hook to fetch all users' achievements for admin panel.
 * @param {boolean} enabled - whether the query should run
 */
export function useAdminUsersAchievements(enabled = false) {
  const { getAllUsersAchievements } = useAchievementsApi();

  const query = useQuery({
    queryKey: ["adminUsersAchievements"],
    queryFn: getAllUsersAchievements,
    enabled,                 // only fetch when enabled
    retry: false,                // retry once on failure (you can adjust)
    refetchInterval: 10000,  // auto-refresh every 10s
    select: (res) => res?.data ?? [], // flatten axios response
  });

  return {
    users: query.data || [],  // query.data is already the array from select
    isLoading: query.isLoading,
    isError: query.isError,
    refetch: query.refetch,
  };
}
