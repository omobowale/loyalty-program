import LoginPage from "../components/admin_panel/LoginPage";
import UsersTable from "../components/admin_panel/UsersTable";
import { useAdminDashboard } from "../hooks/useAdminDashboard";

export default function Admin() {
  const { loggedIn, setLoggedIn, users, isLoading, isError } = useAdminDashboard();

  return (
    <div className="min-h-screen p-8 bg-gray-50">
      <div className="max-w-6xl mx-auto">
        {!loggedIn ? (
          <div className="flex items-center justify-center h-[60vh]">
            <div className="bg-white shadow-md rounded-lg p-8 w-full max-w-md">
              <h1 className="text-xl font-semibold text-gray-800 mb-6 text-center">
                Admin Login
              </h1>
              <LoginPage onLogin={setLoggedIn} />
            </div>
          </div>
        ) : (
          <>
            <h1 className="text-2xl font-bold text-gray-900 mb-6">
              Admin Panel
            </h1>

            {isLoading && (
              <div className="text-center text-gray-600">Loading users...</div>
            )}

            {isError && (
              <div
                role="alert"
                className="text-center text-red-600 font-medium mb-4"
              >
                Failed to fetch user data.
              </div>
            )}

            {!isLoading && !isError && users.length === 0 && (
              <div className="text-center text-gray-600">
                No users found.
              </div>
            )}

            {!isLoading && !isError && users.length > 0 && (
              <UsersTable users={users} />
            )}
          </>
        )}
      </div>
    </div>
  );
}
