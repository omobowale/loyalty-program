<?php

namespace App\Services;

use App\Models\User;

class AdminAchievementService
{
    /**
     * Get all users with achievements and current badge
     */
    public function getAllUserAchievements(): array
    {
        $users = User::with(['achievements', 'badges'])->orderBy('id')->get();

        return $users->map(fn($u) => [
            'user' => [
                'id' => $u->id,
                'name' => $u->name
            ],
            'achievements' => $u->achievements->map(fn($a) => [
                'name' => $a->name,
                'unlocked_at' => $a->pivot->unlocked_at
            ]),
            'current_badge' => optional($u->badges->last())->name
        ])->toArray();
    }
}
