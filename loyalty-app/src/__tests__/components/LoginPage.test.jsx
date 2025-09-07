import { render, screen, fireEvent } from "@testing-library/react"
import { vi } from "vitest"
import LoginPage from "../../components/admin_panel/LoginPage"

// Mock the useMockAuth context
const mockSetUser = vi.fn()
vi.mock("../../context/MockAuthContext", () => ({
  useMockAuth: () => ({
    setUser: mockSetUser,
  }),
}))

describe("LoginPage Component", () => {
  beforeEach(() => {
    mockSetUser.mockClear()
  })

  it("renders password input and login button", () => {
    render(<LoginPage onLogin={vi.fn()} />)
    expect(screen.getByPlaceholderText("Enter password")).toBeInTheDocument()
    expect(screen.getByText("Login")).toBeInTheDocument()
  })

  it("shows error message on incorrect password", () => {
    render(<LoginPage onLogin={vi.fn()} />)

    const input = screen.getByPlaceholderText("Enter password")
    const button = screen.getByText("Login")

    fireEvent.change(input, { target: { value: "wrongpass" } })
    fireEvent.click(button)

    expect(screen.getByRole("alert")).toHaveTextContent("Incorrect password")
    expect(mockSetUser).not.toHaveBeenCalled()
  })

  it("calls onLogin and setUser on correct password", () => {
    const mockOnLogin = vi.fn()
    render(<LoginPage onLogin={mockOnLogin} />)

    const input = screen.getByPlaceholderText("Enter password")
    const button = screen.getByText("Login")

    fireEvent.change(input, { target: { value: "admin123" } })
    fireEvent.click(button)

    expect(mockSetUser).toHaveBeenCalledWith({ id: 1, isAdmin: true })
    expect(mockOnLogin).toHaveBeenCalledWith(true)
    expect(screen.queryByRole("alert")).toBeNull()
  })
})
