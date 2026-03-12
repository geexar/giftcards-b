<?php

namespace App\Repositories;

use App\Models\Faq;

class FaqRepository extends BaseRepository
{
    public function __construct(Faq $model)
    {
        parent::__construct($model);
    }

    public function getPaginatedFaqs()
    {
        return $this->model
            ->latest()
            ->paginate(page: request('page'), perPage: request('per_page'));
    }

    public function getActiveFaqs()
    {
        return $this->model
            ->where('is_active', true)
            ->get();
    }
}
