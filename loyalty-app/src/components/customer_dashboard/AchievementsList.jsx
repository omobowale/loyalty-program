import { useTransition, animated } from "react-spring";
import { CheckCircleIcon, LockClosedIcon } from "@heroicons/react/24/solid";

export default function AchievementsList({ achievements }) {
    // Handle empty list
    if (!achievements || achievements.length === 0) {
        return (
            <div className="bg-white p-6 rounded-xl text-center text-gray-500">
                No achievements yet. Start engaging to unlock achievements! 🎉
            </div>
        );
    }

    const transitions = useTransition(achievements, {
        key: (item) => item.id || item.name, // fallback to name if id is missing
        from: { opacity: 0, transform: "translateY(-8px)" },
        enter: { opacity: 1, transform: "translateY(0)" },
        leave: { opacity: 0, transform: "translateY(-8px)" },
        trail: 100,
    });

    return (
        <div className="bg-white p-6 rounded-xl">
            <div className="space-y-3">
                {transitions((style, a) => (
                    <animated.div style={style} key={a.id || a.name}>
                        <div
                            className={`flex items-center justify-between p-4 rounded-lg border text-sm font-medium ${a.unlocked
                                    ? "bg-green-50 border-green-200 text-green-800"
                                    : "bg-gray-50 border-gray-200 text-gray-500"
                                }`}
                        >
                            <span>{a.name}</span>
                            {a.unlocked ? (
                                <CheckCircleIcon className="w-5 h-5 text-green-600" />
                            ) : (
                                <LockClosedIcon className="w-5 h-5 text-gray-400" />
                            )}
                        </div>
                    </animated.div>
                ))}
            </div>
        </div>
    );
}
