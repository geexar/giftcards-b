<?php

namespace App\Repositories;

use App\Models\PaymentMethod;

class PaymentMethodRepository extends BaseRepository
{
    public function __construct(PaymentMethod $model)
    {
        parent::__construct($model);
    }

    public function getAll(?string $type = null)
    {
        return $this->model
            ->when($type == 'checkout', fn($query) => $query->whereIn('id', [1, 2]))
            ->when($type == 'top_up', fn($query) => $query->whereIn('id', [2, 3]))
            ->get();
    }

    public function getWallet()
    {
        return $this->model->find(1);
    }

    public function getStripe()
    {
        return $this->model->find(2);
    }

    public function getCCPayment()
    {
        return $this->model->find(3);
    }
}
