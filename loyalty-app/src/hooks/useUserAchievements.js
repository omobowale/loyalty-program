import { useState, useEffect, useRef } from "react";
import { useQuery } from "@tanstack/react-query";
import { useAchievementsApi } from "./useAchievementsApi";

export function useUserAchievements(userId) {
    const { getUserAchievements } = useAchievementsApi()
    const [newUnlocked, setNewUnlocked] = useState(null);

    // Load previously seen achievements from localStorage
    const prevUnlockedRef = useRef(
        JSON.parse(localStorage.getItem(`prevUnlockedAchievements_${userId}`) || "[]")
    );

    const query = useQuery({
        queryKey: ["userAchievements", userId],
        queryFn: () => getUserAchievements(userId),
        refetchInterval: 5000,
        staleTime: 4000, // treat cached data as fresh for 4s
        select: (res) => res?.data?.data?.achievements ?? [],
    });

    // Detect newly unlocked achievements
    useEffect(() => {
        const achievements = query.data ?? [];
        if (achievements.length === 0) return;

        const unlockedNames = achievements.map((a) => a.name);

        // Find achievements that are truly new
        const newItems = unlockedNames.filter(
            (name) => !prevUnlockedRef.current.includes(name)
        );

        if (newItems.length > 0) {
            const latestUnlocked = achievements.find(
                (a) => a.name === newItems[newItems.length - 1]
            );

            // Only update state if actually new
            setNewUnlocked((prev) =>
                prev?.name !== latestUnlocked.name ? latestUnlocked : prev
            );
        }

        // Update ref & localStorage only if changed
        if (JSON.stringify(prevUnlockedRef.current) !== JSON.stringify(unlockedNames)) {
            prevUnlockedRef.current = unlockedNames;
            localStorage.setItem(
                `prevUnlockedAchievements_${userId}`,
                JSON.stringify(unlockedNames)
            );
        }
    }, [query.data, userId]);

    // Auto-hide alert after 5 seconds
    useEffect(() => {
        if (!newUnlocked) return;
        const timer = setTimeout(() => setNewUnlocked(null), 5000);
        return () => clearTimeout(timer);
    }, [newUnlocked]);

    return {
        achievements: query.data ?? [],
        badge: query.data?.find(a => a.current_badge)?.current_badge ?? null,
        cashback: query.data?.find(a => a.cashback_balance)?.cashback_balance ?? 0,
        newUnlocked,
        isLoading: query.isLoading,
        isError: query.isError,
        refetch: query.refetch,
    };
}
