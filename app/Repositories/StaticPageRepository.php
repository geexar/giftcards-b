<?php

namespace App\Repositories;

use App\Models\StaticPage;

class StaticPageRepository extends BaseRepository
{
    public function __construct(StaticPage $model)
    {
        parent::__construct($model);
    }

    public function getAll()
    {
        return $this->model->get();
    }
}
