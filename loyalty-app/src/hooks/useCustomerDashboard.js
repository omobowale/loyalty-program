import { useUserAchievements } from "../hooks/useUserAchievements";
import { useMockAuth } from "../context/MockAuthContext";

export function useCustomerDashboard() {
    const { user } = useMockAuth();

    // Early return if user is not loaded
    const { achievements, badge, cashback, newUnlocked, isLoading, isError } =
        user ? useUserAchievements(user.id) : {
            achievements: [],
            badge: null,
            cashback: 0,
            newUnlocked: null,
            isLoading: false,
            isError: false
        };

    return {
        user,
        achievements,
        badge,
        cashback,
        newUnlocked,
        isLoading,
        isError
    };
}
