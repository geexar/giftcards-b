<?php

namespace App\Repositories;

use App\Models\Cart;
use App\Models\User;

class CartRepository extends BaseRepository
{
    public function __construct(Cart $model)
    {
        parent::__construct($model);
    }

    public function getByGuestToken(string $guestToken)
    {
        return $this->model
            ->with('items.product.media', 'items.variantValue')
            ->firstOrCreate(['guest_token' => $guestToken]);
    }

    public function getByUserId(int $userId)
    {
        return $this->model
            ->with('items.product.media', 'items.variantValue')
            ->firstOrCreate(['user_id' => $userId]);
    }

    public function mergeGuestCart(User $user, string $guestToken)
    {
        $this->model->where('guest_token', $guestToken)->update(['user_id' => $user->id]);
    }
}
