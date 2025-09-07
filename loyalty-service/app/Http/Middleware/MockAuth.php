<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MockAuth
{
    public function handle(Request $request, Closure $next, $role = null)
    {
        // Simulate user being "logged in" via query param
        $userId = $request->header('X-Mock-User') ?? $request->query('mock_user');

        if (!$userId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = \App\Models\User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Optional role check
        if ($role === 'admin' && !$user->is_admin) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        // Attach mock user to request
        $request->merge(['mock_user' => $user]);

        return $next($request);
    }
}
