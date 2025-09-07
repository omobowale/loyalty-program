import { render, screen } from "@testing-library/react"
import CashbackDisplay from "../../components/customer_dashboard/CashbackDisplay"

describe("CashbackDisplay Component", () => {
    it("renders the cashback balance correctly", () => {
        const balance = 1234.56
        render(<CashbackDisplay balance={balance} />)

        // Check header
        expect(screen.getByText("Cashback Balance")).toBeInTheDocument()
        // Check balance text
        const balanceElement = screen.getByText("₦1234.56")
        expect(balanceElement).toBeInTheDocument()
        expect(balanceElement).toHaveClass("text-2xl font-bold text-green-700")
        // Check subtext
        expect(screen.getByText("Available Cashback")).toBeInTheDocument()
        expect(screen.getByText("Available Cashback")).toHaveClass("text-sm text-green-600")
    })

    it("renders zero balance correctly", () => {
        render(<CashbackDisplay balance={0} />)

        const balanceElement = screen.getByText("₦0.00")
        expect(balanceElement).toBeInTheDocument()
    })
})
