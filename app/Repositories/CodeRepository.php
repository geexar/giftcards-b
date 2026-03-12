<?php

namespace App\Repositories;

use App\Models\Code;
use App\Models\Product;

class CodeRepository extends BaseRepository
{
    public function __construct(Code $model)
    {
        parent::__construct($model);
    }

    public function getByCodeHash(string $codeHash)
    {
        return $this->model->where('code_hash', $codeHash)->first();
    }

    public function getByReferenceId(string $referenceId)
    {
        return $this->model->where('reference_id', $referenceId)->first();
    }

    public function markAsUsed(array $ids)
    {
        return $this->model
            ->whereIn('id', $ids)
            ->update(['is_used' => true]);
    }

    public function markAsUnused(array $ids)
    {
        return $this->model
            ->whereIn('id', $ids)
            ->update(['is_used' => false]);
    }
}
