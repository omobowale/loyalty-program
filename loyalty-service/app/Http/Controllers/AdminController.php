<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AdminAchievementService;
use Illuminate\Http\Request;

class AdminController extends BaseController
{
    protected AdminAchievementService $adminAchievementService;

    public function __construct(AdminAchievementService $adminAchievementService)
    {
        $this->adminAchievementService = $adminAchievementService;
    }

    public function allAchievements()
    {
        return $this->safeCall(function () {
            $data = $this->adminAchievementService->getAllUserAchievements();
            return $this->successResponse($data, 'All user achievements retrieved successfully');
        }, 'Failed to fetch all user achievements');
    }
}
