import { TrophyIcon } from "@heroicons/react/24/solid"
import AchievementsList from "../components/customer_dashboard/AchievementsList"
import BadgeStatus from "../components/customer_dashboard/BadgeStatus"
import CashbackDisplay from "../components/customer_dashboard/CashbackDisplay"
import { useUserAchievements } from "../hooks/useUserAchievements"

export default function Customer() {
    const userId = 1 // Mock logged-in user
    const { achievements, badge, cashback, newUnlocked, isLoading, isError } =
        useUserAchievements(userId)

    if (isLoading) {
        return <div className="p-8 text-center text-gray-500">Loading...</div>
    }

    if (isError) {
        return (
            <div className="p-8 text-center text-red-500">
                Failed to load data. Please try again.
            </div>
        )
    }

    return (
        <div className="min-h-screen bg-gray-50 py-8 px-4">
            <div className="max-w-6xl mx-auto">
                {/* Header */}
                <h1 className="text-2xl font-semibold text-gray-800 mb-8">
                    Customer Dashboard
                </h1>

                {/* New achievement alert */}
                {newUnlocked && (
                    <div className="flex items-center gap-3 p-4 mb-8 border border-yellow-300 bg-yellow-50 rounded-xl text-yellow-800 font-medium shadow-sm">
                        <TrophyIcon className="h-6 w-6 text-yellow-500" />
                        <span>
                            🎉 Achievement unlocked:{" "}
                            <span className="font-semibold">{newUnlocked.name}</span>
                        </span>
                    </div>
                )}

                {/* Dashboard grid */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {/* Left column: Achievements */}
                    <div className="md:col-span-2">
                        <div className="bg-white rounded-xl shadow-sm p-6">
                            <h2 className="text-lg font-semibold text-gray-700 mb-4">
                                Your Achievements
                            </h2>
                            <AchievementsList achievements={achievements} />
                        </div>
                    </div>

                    {/* Right column: Badge + Cashback */}
                    <div className="space-y-6">
                        <div className="bg-white rounded-xl shadow-sm p-6">
                            <BadgeStatus badge={badge} />
                        </div>
                        <div className="bg-white rounded-xl shadow-sm p-6">
                            <CashbackDisplay balance={cashback} />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    )
}
