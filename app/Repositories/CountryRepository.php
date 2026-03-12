<?php

namespace App\Repositories;

use App\Models\Country;

class CountryRepository extends BaseRepository
{
    public function __construct(Country $model)
    {
        parent::__construct($model);
    }

    public function getAll()
    {
        return $this->model->get();
    }

    public function getByDialCode(string $dial_code)
    {
        return $this->model->where('dial_code', $dial_code)->first();
    }
}
