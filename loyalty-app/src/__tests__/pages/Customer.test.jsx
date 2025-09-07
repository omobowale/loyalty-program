import { render, screen, within } from "@testing-library/react"
import { vi } from "vitest"
import Customer from "../../pages/Customer"
import * as CustomerHook from "../../hooks/useCustomerDashboard"

// Mock the dashboard hook
vi.mock("../../hooks/useCustomerDashboard")

// Mock child components
vi.mock("../../components/customer_dashboard/UserLoginPage", () => ({
    default: () => <button>Mock Login</button>,
}))

vi.mock("../../components/customer_dashboard/AchievementsList", () => ({
    default: ({ achievements }) => (
        <div>
            {achievements.map((a) => (
                <div key={a.id}>{a.name}</div>
            ))}
        </div>
    ),
}))

vi.mock("../../components/customer_dashboard/BadgeStatus", () => ({
    default: ({ badge }) => <div>{badge}</div>,
}))

vi.mock("../../components/customer_dashboard/CashbackDisplay", () => ({
    default: ({ balance }) => <div>{balance.toFixed(2)}</div>,
}))

describe("Customer Page", () => {
    beforeEach(() => {
        vi.clearAllMocks()
    })

    it("shows login page if no user", () => {
        CustomerHook.useCustomerDashboard.mockReturnValue({
            user: null,
            achievements: [],
            badge: null,
            cashback: 0,
            newUnlocked: null,
            isLoading: false,
            isError: false,
        })

        render(<Customer />)
        expect(screen.getByText("Customer Login")).toBeInTheDocument()
        expect(screen.getByText("Mock Login")).toBeInTheDocument()
    })

    it("shows loading dashboard when data is loading", () => {
        CustomerHook.useCustomerDashboard.mockReturnValue({
            user: { id: 1, name: "Alice" },
            achievements: [],
            badge: null,
            cashback: 0,
            newUnlocked: null,
            isLoading: true,
            isError: false,
        })

        render(<Customer />)
        expect(screen.getByText("Loading dashboard...")).toBeInTheDocument()
    })

    it("shows error message if data fails to load", () => {
        CustomerHook.useCustomerDashboard.mockReturnValue({
            user: { id: 1, name: "Alice" },
            achievements: [],
            badge: null,
            cashback: 0,
            newUnlocked: null,
            isLoading: false,
            isError: true,
        })

        render(<Customer />)
        expect(
            screen.getByText("Failed to load data. Please try again.")
        ).toBeInTheDocument()
    })

    it("renders achievements, badge, and cashback correctly", () => {
        CustomerHook.useCustomerDashboard.mockReturnValue({
            user: { id: 1, name: "Alice" },
            achievements: [{ id: 1, name: "First Purchase", unlocked_at: "2025-09-07T10:00:00Z" }],
            badge: "Gold",
            cashback: 500,
            newUnlocked: { name: "First Purchase" },
            isLoading: false,
            isError: false,
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
        expect(screen.getByText(/500/)).toBeInTheDocument()
    })
})
