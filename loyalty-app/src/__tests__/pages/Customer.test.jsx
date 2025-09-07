import { render, screen, within } from "@testing-library/react"
import { vi } from "vitest"
import Customer from "../../pages/Customer"
import * as AchievementsHook from "../../hooks/useUserAchievements"
import * as MockAuth from "../../context/MockAuthContext"

// Mock Achievements hook
const mockAchievements = [
    { id: 1, name: "First Purchase", unlocked_at: "2025-09-07T10:00:00Z" }
]

vi.mock("../../hooks/useUserAchievements")
vi.mock("../../context/MockAuthContext")

describe("Customer Page", () => {
    beforeEach(() => {
        vi.clearAllMocks()
    })

    it("shows loading message if user is not loaded", () => {
        MockAuth.useMockAuth.mockReturnValue({ user: null })

        render(<Customer />)
        expect(screen.getByText("Loading user...")).toBeInTheDocument()
    })

    it("shows loading dashboard if achievements are loading", () => {
        MockAuth.useMockAuth.mockReturnValue({ user: { id: 1, name: "Alice" } })
        AchievementsHook.useUserAchievements.mockReturnValue({
            achievements: [],
            badge: null,
            cashback: 0,
            newUnlocked: null,
            isLoading: true,
            isError: false
        })

        render(<Customer />)
        expect(screen.getByText("Loading dashboard...")).toBeInTheDocument()
    })

    it("shows error message if achievements failed to load", () => {
        MockAuth.useMockAuth.mockReturnValue({ user: { id: 1, name: "Alice" } })
        AchievementsHook.useUserAchievements.mockReturnValue({
            achievements: [],
            badge: null,
            cashback: 0,
            newUnlocked: null,
            isLoading: false,
            isError: true
        })

        render(<Customer />)
        expect(screen.getByText("Failed to load data. Please try again.")).toBeInTheDocument()
    })

    it("renders achievements, badge, and cashback correctly", () => {
        MockAuth.useMockAuth.mockReturnValue({ user: { id: 1, name: "Alice" } })
        AchievementsHook.useUserAchievements.mockReturnValue({
            achievements: mockAchievements,
            badge: "Gold",
            cashback: 500,
            newUnlocked: { name: "First Purchase" },
            isLoading: false,
            isError: false
        })

        render(<Customer />)

        // Check new achievement alert
        const alert = screen.getByRole("alert")
        expect(within(alert).getByText("First Purchase")).toBeInTheDocument()

        // Check achievements list
        const achievementsSection = screen.getByText("Your Achievements").parentElement
        expect(within(achievementsSection).getByText("First Purchase")).toBeInTheDocument()
        // Badge and cashback
        expect(screen.getByText("Gold")).toBeInTheDocument()
        expect(screen.getByText(/500\.00/)).toBeInTheDocument()
    })
})
