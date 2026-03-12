<?php

namespace App\Repositories;

use App\Models\ContactMessage;

class ContactMessageRepository extends BaseRepository
{
    public function __construct(ContactMessage $model)
    {
        parent::__construct($model);
    }
}
