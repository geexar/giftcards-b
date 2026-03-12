<?php

namespace App\Repositories;

use App\Models\Integration;

class IntegrationRepository extends BaseRepository
{
    public function __construct(Integration $model)
    {
        parent::__construct($model);
    }

    public function getLiratGitCards()
    {
        return $this->model->find(1);
    }
}
