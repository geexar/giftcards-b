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

    public function getPaginatedCountries()
    {
        return $this->model
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name->en', 'like', "%{$search}%")
                        ->orWhere('name->ar', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when(request()->has('is_active'), fn($query) => $query->where('is_active', request('is_active')))
            ->paginate(page: request('page'), perPage: request('per_page'));
    }

    public function getByDialCode(string $dial_code)
    {
        return $this->model->where('dial_code', $dial_code)->first();
    }

    public function getDdl()
    {
        return $this->model
            ->where('is_active', true)
            ->get();
    }
}
