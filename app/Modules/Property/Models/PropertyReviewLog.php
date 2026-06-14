<?php

namespace App\Modules\Property\Models;

use App\Modules\Property\Enums\PropertyReviewAction;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyReviewLog extends Model
{
    public const CREATED_AT = 'property_review_created_at';

    public const UPDATED_AT = null;

    protected $primaryKey = 'property_review_log_id';

    protected $table = 'property_review_logs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'property_id',
        'property_review_action',
        'property_review_notes',
        'property_review_performed_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'property_review_action' => PropertyReviewAction::class,
            'property_review_created_at' => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'property_review_performed_by_user_id', 'user_id');
    }
}
