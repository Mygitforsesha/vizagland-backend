<?php

namespace App\Modules\Lead\Models;

use App\Modules\FollowUp\Models\FollowUp;
use App\Modules\Lead\Enums\LeadSource;
use App\Modules\Lead\Enums\LeadStatus;
use App\Modules\Property\Models\Property;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    protected $primaryKey = 'lead_id';

    protected $table = 'leads';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'lead_name',
        'lead_email',
        'lead_phone',
        'lead_message',
        'lead_source',
        'lead_status',
        'lead_property_id',
        'lead_created_by',
        'lead_assigned_to',
    ];

    protected function casts(): array
    {
        return [
            'lead_source' => LeadSource::class,
            'lead_status' => LeadStatus::class,
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'lead_property_id', 'property_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_created_by', 'user_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_assigned_to', 'user_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(LeadAssignment::class, 'lead_id', 'lead_id');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'follow_up_lead_id', 'lead_id');
    }
}
