// src/hooks/useAdminDashboard.js
import { useMockAuth } from "../context/MockAuthContext";
import { useAdminUsersAchievements } from "../hooks/useAdminUsersAchievements";

export function useAdminDashboard() {
    const { user, loggedIn } = useMockAuth();

    // Only fetch if logged in and admin
    const shouldFetch = loggedIn && user?.isAdmin;

    const { users: usersData, isLoading, isError } = useAdminUsersAchievements(shouldFetch);

    return {
        user,
        loggedIn,
        users: shouldFetch ? usersData?.data || [] : [],
        isLoading: shouldFetch ? isLoading : false,
        isError: shouldFetch ? isError : false,
    };
}