<?php

namespace App\Modules\ActivityLog\Models;

use App\Modules\ActivityLog\Enums\ActivityLogType;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public const CREATED_AT = 'activity_log_created_at';

    public const UPDATED_AT = null;

    protected $primaryKey = 'activity_log_id';

    protected $table = 'activity_logs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'activity_log_user_id',
        'activity_log_user_name',
        'activity_log_user_role',
        'activity_log_type',
        'activity_log_action',
        'activity_log_description',
        'activity_log_entity_type',
        'activity_log_entity_id',
        'activity_log_ip_address',
        'activity_log_user_agent',
        'activity_log_metadata',
        'activity_log_created_at',
    ];

    protected function casts(): array
    {
        return [
            'activity_log_type' => ActivityLogType::class,
            'activity_log_metadata' => 'array',
            'activity_log_created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'activity_log_user_id', 'user_id');
    }

    public function actionLabel(): string
    {
        return match ($this->activity_log_type->value.'.'.$this->activity_log_action) {
            'authentication.login' => 'Logged In',
            'authentication.logout' => 'Logged Out',
            'authentication.registered' => 'Registered',
            'authentication.password_changed' => 'Password Changed',
            'authentication.password_reset' => 'Password Reset',
            'property.created' => 'Property Created',
            'property.updated' => 'Property Updated',
            'property_review.submitted_for_review' => 'Submitted For Review',
            'property_review.request_changes' => 'Requested Changes',
            'property_review.approved' => 'Property Approved',
            'property_review.rejected' => 'Property Rejected',
            'property_review.archived' => 'Property Archived',
            'property_review.restored' => 'Property Restored',
            'report.generated' => 'Export Generated',
            'user.created' => 'User Created',
            'user.updated' => 'User Updated',
            'user.activated' => 'User Activated',
            'user.deactivated' => 'User Deactivated',
            'lead.created' => 'Lead Created',
            'lead.updated' => 'Lead Updated',
            'lead.assigned' => 'Lead Assigned',
            'follow_up.created' => 'Follow-up Created',
            'follow_up.updated' => 'Follow-up Updated',
            'system.form_field_added' => 'Form Field Added',
            'system.form_field_disabled' => 'Form Field Disabled',
            default => ucwords(str_replace('_', ' ', $this->activity_log_action)),
        };
    }
}
