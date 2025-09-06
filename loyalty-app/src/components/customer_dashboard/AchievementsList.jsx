import { useSpring, animated } from "react-spring"
import { CheckCircleIcon, LockClosedIcon } from "@heroicons/react/24/solid"

export default function AchievementsList({ achievements }) {
    return (
        <div className="bg-white p-6 rounded-xl">
            
            <div className="space-y-3">
                {achievements.map((a, idx) => {
                    const props = useSpring({
                        from: { opacity: 0, transform: "translateY(-8px)" },
                        to: { opacity: 1, transform: "translateY(0)" },
                        delay: idx * 100,
                    })

                    return (
                        <animated.div key={idx} style={props}>
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
                    )
                })}
            </div>
        </div>
    )
}
