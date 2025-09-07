// src/components/Layout.jsx
import { Link, Outlet, useLocation, useNavigate } from "react-router-dom";
import { useMockAuth } from "../../context/MockAuthContext";

export default function Layout() {
  const location = useLocation();
  const navigate = useNavigate();
  const { user, logout } = useMockAuth();

  const handleLogout = () => {
    logout();
    navigate("/customer"); // redirect to home/login after logout
  };

  return (
    <div className="min-h-screen flex flex-col">
      {/* Navbar */}
      <nav className="bg-blue-600 text-white px-6 py-3 shadow-sm">
        <div className="max-w-7xl mx-auto flex justify-between items-center">
          <h1 className="text-lg font-semibold">Loyalty Program</h1>
          <div className="space-x-4">
            {/* Customer link always visible */}
            <Link
              to="/customer"
              className={`px-3 py-1.5 rounded transition ${
                location.pathname === "/customer"
                  ? "bg-blue-500 font-medium"
                  : "hover:bg-blue-500"
              }`}
            >
              Customer
            </Link>

            {/* Admin link only visible if user is admin */}
            {user?.isAdmin && (
              <Link
                to="/admin"
                className={`px-3 py-1.5 rounded transition ${
                  location.pathname === "/admin"
                    ? "bg-blue-500 font-medium"
                    : "hover:bg-blue-500"
                }`}
              >
                Admin
              </Link>
            )}

            {/* Logout button when logged in */}
            {user && (
              <button
                onClick={handleLogout}
                className="px-3 py-1.5 rounded bg-red-500 hover:bg-red-600 transition"
              >
                Logout
              </button>
            )}
          </div>
        </div>
      </nav>

      {/* Page content */}
      <main className="flex-1 max-w-7xl mx-auto w-full px-6 py-8">
        <Outlet />
      </main>

      {/* Footer */}
      <footer className="bg-gray-100 text-gray-600 text-center py-3 text-sm">
        © {new Date().getFullYear()} Loyalty Program. All rights reserved.
      </footer>
    </div>
  );
}
