<?php

namespace App\Services;

use App\Repositories\ActivityLogRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class ActivityLogService
{
    public function __construct(private ActivityLogRepository $activityLogRepository) {}

    public function store(?Model $model, string $messageKey, Model|string|null $actor = null): void
    {
        $actorInfo = $this->resolveActor($actor);

        $placeholders = [
            ':name' => $actorInfo['name'],
        ];

        $description = [
            'en' => strtr(__('activity.' . $messageKey, [], 'en'), $placeholders),
            'ar' => strtr(__('activity.' . $messageKey, [], 'ar'), $placeholders),
        ];

        $this->activityLogRepository->create([
            'actor_id'   => $actorInfo['actor_id'],
            'actor_type' => $actorInfo['actor_type'],

            'model_id'   => $model?->id,
            'model_type' => $model ? get_class($model) : null,

            'description' => $description,
            'ip_address'  => request()?->ip(),
        ]);
    }

    private function resolveActor(Model|string|null $actor): array
    {
        // 1) Default to authenticated user
        if ($actor === null) {
            $actor = Auth::user();
        }

        // 2) System actor
        if ($actor === 'system') {
            return [
                'actor_id'   => null,
                'actor_type' => null,
                'name'       => 'System',
            ];
        }

        // 3) Normal actor (Admin/User/etc.)
        if (! $actor instanceof Model) {
            throw new InvalidArgumentException('Actor must be a model instance, "system", or null.');
        }

        return [
            'actor_id'   => $actor->id,
            'actor_type' => get_class($actor),
            'name'       => $actor->name,
        ];
    }
}
