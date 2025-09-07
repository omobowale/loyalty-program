import { renderHook, waitFor } from "@testing-library/react";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { vi } from "vitest";
import { useAdminUsersAchievements } from "../../hooks/useAdminUsersAchievements";
import { MockAuthProvider } from "../../context/MockAuthContext";
import * as AchievementsHook from "../../hooks/useAchievementsApi";
import * as AchievementsApi from "../../hooks/useAchievementsApi";


// Mock service
const mockUsers = [
    {
        user: { id: 1, name: "Alice" },
        achievements: [{ id: 1, unlocked_at: "2025-09-07T10:00:00Z" }],
        current_badge: "Gold",
    },
];

vi.mock("../../hooks/useAchievementsApi", () => ({
    useAchievementsApi: vi.fn(),
}));


// Mock the hook to return our fake function
vi.spyOn(AchievementsHook, "useAchievementsApi").mockReturnValue({
    getAllUsersAchievements: vi.fn().mockResolvedValue({ data: mockUsers }),
    getUserAchievements: vi.fn(),
});

// Wrapper for React Query + MockAuth
const createWrapper = () => {
    const queryClient = new QueryClient();
    return ({ children }) => (
        <MockAuthProvider>
            <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>
        </MockAuthProvider>
    );
};

describe("useAdminUsersAchievements Hook", () => {
    afterEach(() => vi.clearAllMocks());

    it("fetches users successfully when enabled is true", async () => {
        const { result } = renderHook(() => useAdminUsersAchievements(true), {
            wrapper: createWrapper(),
        });

        await waitFor(() => expect(result.current.users).toEqual(mockUsers));
        expect(result.current.isLoading).toBe(false);
        expect(result.current.isError).toBe(false);
    });

    it("does not fetch when enabled is false", async () => {
        const { result } = renderHook(() => useAdminUsersAchievements(false), {
            wrapper: createWrapper(),
        });

        expect(result.current.users).toEqual([]);
        expect(result.current.isLoading).toBe(false);
        expect(result.current.isError).toBe(false);
    });

    it("handles API errors correctly", async () => {
    const mockFn = vi.fn().mockRejectedValue(new Error("API Error"));
    AchievementsHook.useAchievementsApi.mockReturnValue({
        getAllUsersAchievements: mockFn,
        getUserAchievements: vi.fn(),
    });

    const { result } = renderHook(() => useAdminUsersAchievements(true), {
        wrapper: createWrapper(),
    });

    // Wait until isError becomes true
    await waitFor(() => expect(result.current.isError).toBe(true));
    expect(result.current.users).toEqual([]);
});
});
