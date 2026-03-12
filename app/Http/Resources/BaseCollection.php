<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BaseCollection extends ResourceCollection
{
    public function __construct($items, string $resource, $extra = null)
    {
        $this->collects = $resource;
        $this->extra = $extra;
        parent::__construct($items);
    }

    public function toArray($request): array
    {
        return [
            'items' => $this->collection,
            'pagination' => [
                'total' => $this->total(),
                'count' => $this->count(),
                'per_page' => $this->perPage(),
                'next_page_url' => $this->nextPageUrl() ?? '',
                'prev_page_url' => $this->previousPageUrl() ?? '',
                'current_page' => $this->currentPage(),
                'total_pages' => $this->lastPage(),
            ],
            'extra' => $this->extra,
        ];
    }
}
