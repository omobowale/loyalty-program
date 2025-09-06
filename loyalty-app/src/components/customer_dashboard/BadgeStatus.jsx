export default function BadgeStatus({ badge }) {
    return (
        <div className="bg-white p-6 rounded-xl">
            <h2 className="text-lg font-semibold text-gray-800 mb-3">
                Current Badge
            </h2>
            <div className="p-4 border border-gray-200 rounded-lg text-center text-gray-700 font-medium">
                {badge ? (
                    <span className="text-indigo-600 font-bold">{badge}</span>
                ) : (
                    <span className="text-gray-400 italic">No badge yet</span>
                )}
            </div>
        </div>
    )
}
