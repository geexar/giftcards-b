<?php

namespace App\Services\Admin;

use App\Models\StatusUpdateLog;
use App\Repositories\StatusUpdateLogRepository;
use Illuminate\Database\Eloquent\Model;

class StatusUpdateLogService
{
    public function __construct(private StatusUpdateLogRepository $statusUpdateLogRepository) {}

    public function store(Model $model, ?string $oldStatus, string $newStatus, ?Model $actor = null): StatusUpdateLog
    {
        return $this->statusUpdateLogRepository->create([
            'actor_id'   => $actor?->id,
            'actor_type' => $actor ? get_class($actor) : null,
            'model_id'   => $model->id,
            'model_type' => get_class($model),
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'created_at' => now(),
        ]);
    }
}
