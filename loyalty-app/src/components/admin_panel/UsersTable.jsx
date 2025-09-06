export default function UsersTable({ users }) {
  return (
    <div className="overflow-x-auto mt-6">
      <table className="w-full border-collapse">
        <thead className="bg-gray-100 text-gray-700 border-b">
          <tr>
            <th className="px-6 py-3 text-left text-sm font-semibold">
              Name
            </th>
            <th className="px-6 py-3 text-left text-sm font-semibold">
              Achievements Unlocked
            </th>
            <th className="px-6 py-3 text-left text-sm font-semibold">
              Current Badge
            </th>
          </tr>
        </thead>
        <tbody className="divide-y divide-gray-200">
          {users.map((u, i) => (
            <tr
              key={u.user.id}
              className={`${i % 2 === 0 ? "bg-white" : "bg-gray-50"} hover:bg-blue-50`}
            >
              <td className="px-6 py-3 text-left text-sm text-gray-800">
                {u.user.name}
              </td>
              <td className="px-6 py-3 text-left text-sm text-gray-800">
                {u.achievements.filter((a) => a.unlocked_at).length}
              </td>
              <td className="px-6 py-3 text-left text-sm text-gray-800">
                {u.current_badge ? (
                  <span className="inline-block px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs">
                    {u.current_badge}
                  </span>
                ) : (
                  <span className="text-gray-400 italic">None</span>
                )}
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
