<?php

namespace App\Repositories;

use App\Enums\TagType;
use App\Models\Tag;

class TagRepository extends BaseRepository
{
    public function __construct(Tag $model)
    {
        parent::__construct($model);
    }

    public function getTagsByType(TagType $type)
    {
        return $this->model->where('type', $type->value)->get();
    }
}
