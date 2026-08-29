<?php

namespace App\Modules\Property\Resources;

use App\Modules\Property\Models\PropertySearchHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PropertySearchHistory */
class PropertySearchHistoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->property_search_history_id,
            'search_keyword' => $this->property_search_history_keyword,
            'search_filters' => $this->property_search_history_filters ?? (object) [],
            'results_count' => $this->property_search_history_results_count,
            'ip_address' => $this->property_search_history_ip_address,
            'mobile_number' => $this->property_search_history_mobile_number,
            'user' => $this->when(
                $this->relationLoaded('user') && $this->user !== null,
                fn (): array => [
                    'user_id' => $this->user->user_id,
                    'user_full_name' => $this->user->user_full_name,
                    'user_role' => $this->user->user_role?->value,
                ],
            ),
            'created_at' => $this->recordedAt()?->toIso8601String(),
        ];
    }
}
