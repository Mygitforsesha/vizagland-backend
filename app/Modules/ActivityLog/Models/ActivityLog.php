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
        'activity_log_latitude',
        'activity_log_longitude',
        'activity_log_road',
        'activity_log_colony',
        'activity_log_suburb',
        'activity_log_village',
        'activity_log_mandal',
        'activity_log_district',
        'activity_log_state',
        'activity_log_pincode',
        'activity_log_country',
        'activity_log_metadata',
        'activity_log_created_at',
    ];

    protected function casts(): array
    {
        return [
            'activity_log_type' => ActivityLogType::class,
            'activity_log_metadata' => 'array',
            'activity_log_latitude' => 'decimal:7',
            'activity_log_longitude' => 'decimal:7',
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
            'property.resolved' => 'Property Resolved',
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

    public function locationLabel(): ?string
    {
        $segments = $this->resolvedLocationSegments();

        if ($segments === []) {
            return null;
        }

        return implode(', ', $segments);
    }

    public function googleMapsUrl(): ?string
    {
        $latitude = $this->resolvedLocationValue('latitude');
        $longitude = $this->resolvedLocationValue('longitude');

        if ($latitude === null || $longitude === null) {
            return null;
        }

        return "https://www.google.com/maps?q={$latitude},{$longitude}";
    }

    public function locationSource(): ?string
    {
        if ($this->hasLocationSnapshot()) {
            return 'activity_snapshot';
        }

        return $this->fallbackProfile() !== null ? 'user_current_location' : null;
    }

    public function resolvedLocationValue(string $field): mixed
    {
        if ($this->hasLocationSnapshot()) {
            return $this->getAttribute('activity_log_'.$field);
        }

        $profile = $this->fallbackProfile();

        return $profile?->getAttribute('user_'.$field);
    }

    public function hasLocationSnapshot(): bool
    {
        foreach ($this->snapshotFields() as $field) {
            $value = $this->getAttribute('activity_log_'.$field);

            if (is_string($value) && trim($value) !== '') {
                return true;
            }

            if ($value !== null && ! is_string($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function resolvedLocationSegments(): array
    {
        return collect([
            $this->resolvedLocationValue('road'),
            $this->resolvedLocationValue('colony'),
            $this->resolvedLocationValue('suburb'),
            $this->resolvedLocationValue('village'),
            $this->resolvedLocationValue('mandal'),
            $this->resolvedLocationValue('district'),
            $this->resolvedLocationValue('state'),
            $this->resolvedLocationValue('pincode'),
            $this->resolvedLocationValue('country'),
        ])
            ->filter(static fn (mixed $value): bool => is_string($value) && trim($value) !== '')
            ->map(static fn (mixed $value): string => trim((string) $value))
            ->unique(static fn (string $value): string => mb_strtolower($value))
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private function snapshotFields(): array
    {
        return [
            'latitude',
            'longitude',
            'road',
            'colony',
            'suburb',
            'village',
            'mandal',
            'district',
            'state',
            'pincode',
            'country',
        ];
    }

    private function fallbackProfile(): ?\App\Modules\User\Models\UserProfile
    {
        $user = $this->relationLoaded('user') ? $this->user : null;

        if ($user === null) {
            return null;
        }

        return $user->relationLoaded('profile') ? $user->profile : null;
    }
}
