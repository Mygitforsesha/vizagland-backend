<?php

namespace App\Modules\Property\Models;

use App\Modules\Property\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyReview extends Model
{
    protected $primaryKey = 'property_review_id';

    protected $table = 'property_reviews';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'property_id',
        'reviewed_by',
        'review_status',
        'review_comments',
    ];

    protected function casts(): array
    {
        return [
            'review_status' => ReviewStatus::class,
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }
}
