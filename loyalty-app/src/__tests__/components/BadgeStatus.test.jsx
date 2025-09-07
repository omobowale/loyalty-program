// src/components/customer_dashboard/__tests__/BadgeStatus.test.jsx
import { render, screen } from "@testing-library/react"
import BadgeStatus from "../../components/customer_dashboard/BadgeStatus"

describe("BadgeStatus Component", () => {
    it("renders the badge when provided", () => {
        const badgeName = "Gold"
        render(<BadgeStatus badge={badgeName} />)

        // Check header
        expect(screen.getByText("Current Badge")).toBeInTheDocument()
        // Check badge value
        const badgeElement = screen.getByText(badgeName)
        expect(badgeElement).toBeInTheDocument()
        // Optional: check for styling class
        expect(badgeElement).toHaveClass("text-indigo-600 font-bold")
    })

    it("renders empty state when no badge is provided", () => {
        render(<BadgeStatus badge={null} />)

        // Check header
        expect(screen.getByText("Current Badge")).toBeInTheDocument()
        // Check empty state text
        const emptyElement = screen.getByText("No badge yet")
        expect(emptyElement).toBeInTheDocument()
        expect(emptyElement).toHaveClass("text-gray-400 italic")
    })
})
