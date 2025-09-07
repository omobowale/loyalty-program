import { render, screen, fireEvent } from "@testing-library/react"
import { vi } from "vitest"
import LoginPage from "../../components/admin_panel/LoginPage"

// Mock the useMockAuth context
const mockLogin = vi.fn()
let mockError = null

vi.mock("../../context/MockAuthContext", () => ({
  useMockAuth: () => ({
    login: mockLogin,
    error: mockError,
    loggedIn: false,
  }),
}))

describe("LoginPage Component", () => {
  beforeEach(() => {
    mockLogin.mockClear()
    mockError = null
  })

  it("renders password input and login button", () => {
    render(<LoginPage />)
    expect(screen.getByPlaceholderText("Enter password")).toBeInTheDocument()
    expect(screen.getByText("Login")).toBeInTheDocument()
  })

  it("calls login with correct arguments when password entered", () => {
    render(<LoginPage />)

    const input = screen.getByPlaceholderText("Enter password")
    const button = screen.getByText("Login")

    fireEvent.change(input, { target: { value: "admin123" } })
    fireEvent.click(button)

    expect(mockLogin).toHaveBeenCalledWith({ password: "admin123", userType: "admin" })
  })

  it("shows error message if error is returned from context", () => {
    mockError = "Incorrect password"
    render(<LoginPage />)

    expect(screen.getByRole("alert")).toHaveTextContent("Incorrect password")
  })
})
