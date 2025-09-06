import { Link, Outlet, useLocation } from "react-router-dom";

export default function Layout() {
    const location = useLocation();

    return (
        <div className="min-h-screen flex flex-col">
            {/* Navbar */}
            <nav className="bg-blue-600 text-white px-6 py-3 shadow-sm">
                <div className="max-w-7xl mx-auto flex justify-between items-center">
                    <h1 className="text-lg font-semibold">Loyalty Program</h1>
                    <div className="space-x-4">
                        <Link
                            to="/customer"
                            className={`px-3 py-1.5 rounded transition ${location.pathname === "/customer"
                                    ? "bg-blue-500 font-medium"
                                    : "hover:bg-blue-500"
                                }`}
                        >
                            Customer
                        </Link>
                        <Link
                            to="/admin"
                            className={`px-3 py-1.5 rounded transition ${location.pathname === "/admin"
                                    ? "bg-blue-500 font-medium"
                                    : "hover:bg-blue-500"
                                }`}
                        >
                            Admin
                        </Link>
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
