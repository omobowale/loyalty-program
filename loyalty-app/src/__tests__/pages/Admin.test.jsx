import { render, screen } from "@testing-library/react";
import { vi } from "vitest";
import Admin from "../../pages/Admin";
import * as AdminHook from "../../hooks/useAdminDashboard";

// Mock child components
vi.mock("../../components/admin_panel/LoginPage", () => ({
    default: ({ onLogin }) => <button onClick={() => onLogin(true)}>Login</button>,
}));

vi.mock("../../components/admin_panel/UsersTable", () => ({
    default: ({ users }) => (
        <div>
            {users.map((u) => (
                <div key={u.user.id}>{u.user.name}</div>
            ))}
        </div>
    ),
}));

describe("Admin Page", () => {
    afterEach(() => {
        vi.clearAllMocks();
    });

    it("renders login page when not logged in", () => {
        vi.spyOn(AdminHook, "useAdminDashboard").mockReturnValue({
            loggedIn: false,
            setLoggedIn: vi.fn(),
            users: [],
            isLoading: false,
            isError: false,
        });

        render(<Admin />);

        expect(screen.getByText(/Admin Login/i)).toBeInTheDocument();
        expect(screen.getByRole("button", { name: /Login/i })).toBeInTheDocument();
    });

    it("shows loading state when fetching users", () => {
        vi.spyOn(AdminHook, "useAdminDashboard").mockReturnValue({
            loggedIn: true,
            setLoggedIn: vi.fn(),
            users: [],
            isLoading: true,
            isError: false,
        });

        render(<Admin />);

        expect(screen.getByText(/Loading users/i)).toBeInTheDocument();
    });

    it("shows error state when API fails", () => {
        vi.spyOn(AdminHook, "useAdminDashboard").mockReturnValue({
            loggedIn: true,
            setLoggedIn: vi.fn(),
            users: [],
            isLoading: false,
            isError: true,
        });

        render(<Admin />);

        expect(screen.getByRole("alert")).toHaveTextContent(/Failed to fetch user data/i);
    });

    it("shows no users message when list is empty", () => {
        vi.spyOn(AdminHook, "useAdminDashboard").mockReturnValue({
            loggedIn: true,
            setLoggedIn: vi.fn(),
            users: [],
            isLoading: false,
            isError: false,
        });

        render(<Admin />);

        expect(screen.getByText(/No users found/i)).toBeInTheDocument();
    });

    it("renders users table when users exist", () => {
        const mockUsers = [
            { user: { id: 1, name: "Alice" } },
            { user: { id: 2, name: "Bob" } },
        ];

        vi.spyOn(AdminHook, "useAdminDashboard").mockReturnValue({
            loggedIn: true,
            setLoggedIn: vi.fn(),
            users: mockUsers,
            isLoading: false,
            isError: false,
        });

        render(<Admin />);

        expect(screen.getByText("Alice")).toBeInTheDocument();
        expect(screen.getByText("Bob")).toBeInTheDocument();
    });
});
