import { useState } from "react"
import { useMockAuth } from "../../context/MockAuthContext"

export default function LoginPage({ onLogin }) {
    const [password, setPassword] = useState("")
    const { user, setUser } = useMockAuth()

    const handleLogin = () => {
        if (password === "admin123") {
            // Mock authentication
            onLogin(true)
            setUser({ id: 1, isAdmin: true })
        } else {
            alert("Wrong password")
        }
    }

    return (
        <div className="w-full">
            <div className="flex flex-col space-y-4">
                <input
                    type="password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    placeholder="Enter password"
                    className="px-4 py-2 border border-gray-200 text-black rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <button
                    onClick={handleLogin}
                    className="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700 transition duration-200"
                >
                    Login
                </button>
            </div>
        </div>
    )
}
