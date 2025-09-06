import { useState, useRef } from "react";
import { useQuery } from "@tanstack/react-query";
import { getUserAchievements } from "../services/customers";

export function useUserAchievements(userId) {
    const [newUnlocked, setNewUnlocked] = useState(null);
    const prevUnlockedNamesRef = useRef([]);
    const initializedRef = useRef(false);

    const query = useQuery({
        queryKey: ["userAchievements", userId],
        queryFn: () => getUserAchievements(userId),
        refetchInterval: 5000,
        onSuccess: (res) => {
            const data = res?.data?.data?.data; // <- fixed path
            if (!data) return;

            const achievements = data.achievements ?? [];

            if (!initializedRef.current) {
                prevUnlockedNamesRef.current = achievements
                    .filter((a) => a.unlocked)
                    .map((a) => a.name);
                initializedRef.current = true;
                return;
            }

            const newlyUnlocked = achievements.filter(
                (a) =>
                    a.unlocked && !prevUnlockedNamesRef.current.includes(a.name)
            );

            if (newlyUnlocked.length > 0) {
                setNewUnlocked(newlyUnlocked[0]);
            }

            prevUnlockedNamesRef.current = achievements
                .filter((a) => a.unlocked)
                .map((a) => a.name);
        },
    });

    const payload = query.data?.data?.data ?? {}; // <- safe extract

    return {
        achievements: payload.achievements ?? [],
        badge: payload.current_badge ?? null,
        cashback: payload.cashback_balance ?? 0,
        newUnlocked,
        isLoading: query.isLoading,
        isError: query.isError,
        refetch: query.refetch,
    };
}
