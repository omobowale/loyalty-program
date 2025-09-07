<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MockAuth
{
    public function handle(Request $request, Closure $next, $role = null)
    {
        // Simulate user being "logged in" via query param
        $userId = $request->header('X-Mock-User') ?? $request->query('mock_user');

        if (!$userId) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.',
            ], 404);
        }

        // Role check
        if ($role === 'admin' && $user->role !== "admin") {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden.',
            ], 403);
        }

        // Attach mock user to request
        $request->merge(['mock_user' => $user]);

        return $next($request);
    }
}
