<?php

namespace App\Modules\MasterLocation\Resources;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class MasterLocationSearchResource extends JsonResource
{
    /**
     * @param  Collection<int, mixed>|LengthAwarePaginator<int, mixed>  $resource
     */
    public function __construct(
        private readonly Collection|LengthAwarePaginator $results,
    ) {
        parent::__construct($results);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $payload = [
            'items' => MasterLocationResource::collection($this->results)->resolve(),
        ];

        if ($this->results instanceof LengthAwarePaginator) {
            $payload['pagination'] = [
                'current_page' => $this->results->currentPage(),
                'per_page' => $this->results->perPage(),
                'total' => $this->results->total(),
                'last_page' => $this->results->lastPage(),
            ];
        }

        return $payload;
    }
}
