import { useState } from "react"
import LoginPage from "../components/admin_panel/LoginPage"
import { useAdminUsersAchievements } from "../hooks/useAdminUsersAchievements"
import UsersTable from "../components/admin_panel/UsersTable"

export default function Admin() {
  const [loggedIn, setLoggedIn] = useState(false)
  const { users, isLoading, isError } = useAdminUsersAchievements(loggedIn)

  return (
    <div className="min-h-screen p-8">
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
              <div className="text-center text-red-600">
                Failed to fetch user data.
              </div>
            )}
            {!isLoading && !isError && <UsersTable users={users} />}
          </>
        )}
      </div>
    </div>
  )
}
