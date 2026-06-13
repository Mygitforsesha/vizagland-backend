<?php

namespace App\Modules\Property\Resources;

use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class PropertyCreatorResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->user_id,
            'user_full_name' => $this->user_full_name,
            'user_email' => $this->user_email,
            'user_phone' => $this->user_phone,
            'user_role' => $this->user_role->value,
            'user_role_label' => $this->user_role->label(),
        ];
    }
}
