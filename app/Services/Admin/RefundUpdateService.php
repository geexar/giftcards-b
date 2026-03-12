<?php

namespace App\Services\Admin;

use App\Models\Refund;
use App\Models\RefundUpdate;
use Illuminate\Database\Eloquent\Model;

class RefundUpdateService
{
    public function store(Refund $refund, ?string $oldStatus, string $newStatus, float $amount, ?Model $actor = null): RefundUpdate
    {
        return RefundUpdate::create([
            'refund_id'  => $refund->id,
            'actor_id'   => $actor?->id,
            'actor_type' => $actor ? get_class($actor) : null,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'amount'     => $amount,
            'created_at' => now(),
        ]);
    }
}
