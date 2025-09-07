import { TrophyIcon } from "@heroicons/react/24/solid";
import { useSpring, animated } from "react-spring";
import AchievementsList from "../components/customer_dashboard/AchievementsList";
import BadgeStatus from "../components/customer_dashboard/BadgeStatus";
import CashbackDisplay from "../components/customer_dashboard/CashbackDisplay";
import { useCustomerDashboard } from "../hooks/useCustomerDashboard";

export default function Customer() {
    const { user, achievements, badge, cashback, newUnlocked, isLoading, isError } =
        useCustomerDashboard();

    if (!user) {
        return <div className="p-8 text-center text-gray-500">Loading user...</div>;
    }

    const transitionStyles = useSpring({
        opacity: newUnlocked ? 1 : 0,
        transform: newUnlocked ? "translateY(0px)" : "translateY(-8px)",
    });

    if (isLoading) {
        return <div className="p-8 text-center text-gray-500">Loading dashboard...</div>;
    }

    if (isError) {
        return (
            <div className="p-8 text-center text-red-500">
                Failed to load data. Please try again.
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-gray-50 py-8 px-4">
            <div className="max-w-6xl mx-auto">
                <h1 className="text-2xl font-semibold text-gray-800 mb-8">
                    Customer Dashboard
                </h1>

                {newUnlocked && (
                    <div
                        role="alert"
                        className="flex items-center gap-3 p-4 mb-8 border border-yellow-300 bg-yellow-50 rounded-xl text-yellow-800 font-medium shadow-sm"
                    >
                        <TrophyIcon className="h-6 w-6 text-yellow-500" />
                        <animated.div style={transitionStyles}>
                            🎉 Achievement unlocked:{" "}
                            <span className="font-semibold">{newUnlocked.name}</span>
                        </animated.div>
                    </div>
                )}

                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div className="md:col-span-2">
                        <div className="bg-white rounded-xl shadow-sm p-6">
                            <h2 className="text-lg font-semibold text-gray-700 mb-4">
                                Your Achievements
                            </h2>
                            <AchievementsList achievements={achievements} />
                        </div>
                    </div>

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
    );
}
