// src/components/admin_panel/__tests__/UsersTable.test.jsx
import { render, screen } from "@testing-library/react"
import UsersTable from "../../components/admin_panel/UsersTable"

describe("UsersTable Component", () => {
    const users = [
        {
            user: { id: 1, name: "Alice" },
            achievements: [
                { id: 1, unlocked_at: "2025-09-07T10:00:00Z" },
                { id: 2, unlocked_at: null },
            ],
            current_badge: "Gold",
        },
        {
            user: { id: 2, name: "Bob" },
            achievements: [],
            current_badge: null,
        },
    ]

    it("renders table headers correctly", () => {
        render(<UsersTable users={[]} />)
        expect(screen.getByText("Name")).toBeInTheDocument()
        expect(screen.getByText("Achievements Unlocked")).toBeInTheDocument()
        expect(screen.getByText("Current Badge")).toBeInTheDocument()
    })

    it("renders user rows correctly", () => {
        render(<UsersTable users={users} />)

        // Alice
        expect(screen.getByText("Alice")).toBeInTheDocument()
        expect(screen.getByText("1")).toBeInTheDocument() // 1 unlocked achievement
        expect(screen.getByText("Gold")).toBeInTheDocument()
        expect(screen.getByText("Gold")).toHaveClass(
            "inline-block px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs"
        )

        // Bob
        expect(screen.getByText("Bob")).toBeInTheDocument()
        expect(screen.getByText("0")).toBeInTheDocument() // 0 achievements
        expect(screen.getByText("None")).toBeInTheDocument()
        expect(screen.getByText("None")).toHaveClass("text-gray-400 italic")
    })
})
