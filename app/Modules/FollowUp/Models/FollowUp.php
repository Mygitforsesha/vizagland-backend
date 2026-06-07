<?php

namespace App\Modules\FollowUp\Models;

use App\Modules\FollowUp\Enums\FollowUpStatus;
use App\Modules\FollowUp\Enums\FollowUpType;
use App\Modules\Lead\Models\Lead;
use App\Modules\Property\Models\Property;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowUp extends Model
{
    protected $primaryKey = 'follow_up_id';

    protected $table = 'follow_ups';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'follow_up_type',
        'follow_up_notes',
        'follow_up_scheduled_at',
        'follow_up_completed_at',
        'follow_up_status',
        'follow_up_property_id',
        'follow_up_lead_id',
        'follow_up_created_by',
        'follow_up_assigned_to',
    ];

    protected function casts(): array
    {
        return [
            'follow_up_type' => FollowUpType::class,
            'follow_up_status' => FollowUpStatus::class,
            'follow_up_scheduled_at' => 'datetime',
            'follow_up_completed_at' => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'follow_up_property_id', 'property_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'follow_up_lead_id', 'lead_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follow_up_created_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follow_up_assigned_to');
    }
}
