import { useState, useEffect } from "react";
import { useAdminUsersAchievements } from "../hooks/useAdminUsersAchievements";
import { useMockAuth } from "../context/MockAuthContext";

export function useAdminDashboard() {
    const { user } = useMockAuth();

    const [loggedIn, setLoggedIn] = useState(() => {
        return localStorage.getItem("adminLoggedIn") === "true";
    });

    const { users: usersData, isLoading, isError } = useAdminUsersAchievements(loggedIn);

    // Extract users safely (handle data inside .data)
    const users = usersData?.data || [];

    // Sync login state to localStorage
    useEffect(() => {
        localStorage.setItem("adminLoggedIn", loggedIn);
    }, [loggedIn]);

    // Force logout if user is not admin
    useEffect(() => {
        if (user && !user.isAdmin) {
            setLoggedIn(false);
        }
    }, [user]);

    return {
        user,
        loggedIn,
        setLoggedIn,
        users,
        isLoading,
        isError,
    };
}
