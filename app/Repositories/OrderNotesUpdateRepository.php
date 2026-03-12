<?php

namespace App\Repositories;

use App\Models\OrderNotesUpdate;

class OrderNotesUpdateRepository extends BaseRepository
{
    public function __construct(OrderNotesUpdate $model)
    {
        parent::__construct($model);
    }
}
