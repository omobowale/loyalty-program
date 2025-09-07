<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserAchievementService;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    protected UserAchievementService $userAchievementService;

    public function __construct(UserAchievementService $userAchievementService)
    {
        $this->userAchievementService = $userAchievementService;
    }

    public function achievements(Request $request, $userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        $data = $this->userAchievementService->getUserAchievements($user);

        // Only sum successful transactions
        $data['cashback_balance'] = $user->transactions()
            ->where('status', 'success')
            ->sum('amount');

        return $this->successResponse($data, 'User achievements retrieved successfully');
    }
}
