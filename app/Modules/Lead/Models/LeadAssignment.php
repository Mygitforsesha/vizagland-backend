<?php

namespace App\Modules\Lead\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadAssignment extends Model
{
    protected $primaryKey = 'lead_assignment_id';

    protected $table = 'lead_assignments';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'lead_id',
        'lead_assigned_to',
        'lead_assigned_by',
        'lead_assignment_remarks',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id', 'lead_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_assigned_to', 'user_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_assigned_by', 'user_id');
    }
}
