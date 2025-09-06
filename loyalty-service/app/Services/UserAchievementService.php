<?php

namespace App\Services;

use App\Models\User;

class UserAchievementService
{
    /**
     * Get achievements, badge, and cashback for a user
     */
    public function getUserAchievements(User $user): array
    {
        $totalCashback = $user->transactions()
            ->where('status', 'success')
            ->sum('amount');

        return [
            'achievements' => $user->achievements->map(fn($a) => [
                'name'        => $a->name,
                'unlocked'    => true,
                'unlocked_at' => $a->pivot->unlocked_at
            ]),
            'current_badge'    => optional($user->badges->last())->name,
            'cashback_balance' => $totalCashback
        ];
    }
}
