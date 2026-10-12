<?php

namespace App\Modules\Property\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyOwner extends Model
{
    protected $primaryKey = 'property_owner_id';

    protected $table = 'property_owners';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'property_id',
        'property_owner_name',
        'property_owner_phone',
        'property_owner_email',
        'property_owner_sort_order',
    ];

    protected function casts(): array
    {
        return [
            'property_owner_sort_order' => 'integer',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }
}
