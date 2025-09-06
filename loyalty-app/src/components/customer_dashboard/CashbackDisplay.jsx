export default function CashbackDisplay({ balance }) {
    return (
        <div className="bg-white p-6 rounded-xl">
            <h2 className="text-lg font-semibold text-gray-800 mb-3">
                Cashback Balance
            </h2>
            <div className="p-4 border border-green-200 rounded-lg text-center bg-green-50">
                <p className="text-2xl font-bold text-green-700">
                    ₦{balance.toFixed(2)}
                </p>
                <p className="text-sm text-green-600">Available Cashback</p>
            </div>
        </div>
    )
}
