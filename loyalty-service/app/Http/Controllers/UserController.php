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

    public function achievements(User $user)
    {
        return $this->safeCall(function () use ($user) {
            $data = $this->userAchievementService->getUserAchievements($user);
            return $this->successResponse($data, 'User achievements retrieved successfully');
        }, 'Failed to fetch user achievements');
    }
}
