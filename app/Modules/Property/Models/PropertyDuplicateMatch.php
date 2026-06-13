<?php

namespace App\Modules\Property\Models;

use App\Modules\Property\Enums\DuplicateMatchStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyDuplicateMatch extends Model
{
    protected $primaryKey = 'property_duplicate_match_id';

    protected $table = 'property_duplicate_matches';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'property_id',
        'matched_property_id',
        'property_duplicate_match_percentage',
        'property_duplicate_match_status',
    ];

    protected function casts(): array
    {
        return [
            'property_duplicate_match_percentage' => 'decimal:2',
            'property_duplicate_match_status' => DuplicateMatchStatus::class,
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }

    public function matchedProperty(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'matched_property_id', 'property_id');
    }
}
