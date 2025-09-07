import { render, screen } from "@testing-library/react"
import AchievementsList from "../../components/customer_dashboard/AchievementsList"

// Mock react-spring properly
vi.mock("react-spring", () => ({
    useTransition: (items) => {
        // Return a function that maps over items
        return (renderFn) =>
            items.map((item) => {
                return renderFn({}, item)
            })
    },
    animated: { div: ({ children }) => <div>{children}</div> },
}))

describe("AchievementsList Component", () => {
    it("renders empty state when no achievements are provided", () => {
        render(<AchievementsList achievements={[]} />)

        expect(
            screen.getByText(
                "No achievements yet. Start engaging to unlock achievements! 🎉"
            )
        ).toBeInTheDocument()
    })

    it("renders unlocked and locked achievements correctly", () => {
        const achievements = [
            { id: 1, name: "First Purchase", unlocked: true },
            { id: 2, name: "Invite Friend", unlocked: false },
        ]

        render(<AchievementsList achievements={achievements} />)

        // Check achievement names
        expect(screen.getByText("First Purchase")).toBeInTheDocument()
        expect(screen.getByText("Invite Friend")).toBeInTheDocument()
    })
})