<?php

namespace App\Services\Tracking;
use App\Models\User;
use App\Models\UserActivityLog;

class UserActivityTracker
{
    public function record(
        User $user,
        string $module,
        string $activityType,
        ?string $subjectType = null,
        ?int $subjectId = null,
        array $metadata = []
    ): void {
        UserActivityLog::query()->create([
            'user_id' => $user->getKey(),
            'module' => $module,
            'activity_type' => $activityType,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'metadata' => $metadata ?: null,
            'created_at' => now(),
        ]);
    }

    public function education(
        User $user,
        string $activityType,
        ?string $subjectType = null,
        ?int $subjectId = null
    ): void {
        $this->record(
            $user,
            'education',
            $activityType,
            $subjectType,
            $subjectId
        );
    }

    public function tool(
        User $user,
        string $activityType,
        ?string $subjectType = null,
        ?int $subjectId = null
    ): void {
        $this->record(
            $user,
            'tools',
            $activityType,
            $subjectType,
            $subjectId
        );
    }
}

