<?php

namespace App\Repositories;

use App\Models\ContactMessage;

class ContactMessageRepository extends BaseRepository
{
    public function __construct(ContactMessage $model)
    {
        parent::__construct($model);
    }

    public function getPaginatedMessages()
    {
        return $this->model
            ->when(request('search'), function ($query, $name) {
                return $query->where('name', 'like', "%{$name}%")
                    ->orWhere('email', 'like', "%{$name}%");
            })
            ->latest()
            ->paginate(page: request('page'), perPage: request('per_page'));
    }
}
