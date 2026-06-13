<?php

namespace App\Modules\Property\Models;

use App\Modules\Property\Enums\ReviewStatus;
use App\Modules\User\Models\User;
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
        'property_review_reviewed_by',
        'property_review_status',
        'property_review_comments',
        'property_review_reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'property_review_status' => ReviewStatus::class,
            'property_review_reviewed_at' => 'datetime',
        ];
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'property_review_reviewed_by', 'user_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }
}
