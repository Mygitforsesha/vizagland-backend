<?php

namespace App\Modules\Property\Models;

use App\Modules\Property\Enums\PropertyContactType;
use App\Modules\Property\Enums\PropertyCreatedByType;
use App\Modules\Property\Enums\PropertyListingType;
use App\Modules\Property\Enums\PropertyOwnershipType;
use App\Modules\Property\Enums\PropertyRecordType;
use App\Modules\Property\Enums\PropertySource;
use App\Modules\Property\Enums\PropertyStatus;
use App\Modules\Property\Enums\PropertyType;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Property extends Model
{
    protected $primaryKey = 'property_id';

    protected $table = 'properties';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'property_reference_id',
        'property_record_type',
        'property_parent_property_id',
        'property_title',
        'property_description',
        'property_type',
        'property_listing_type',
        'property_price',
        'property_negotiable',
        'property_area_sqft',
        'property_area',
        'property_area_unit',
        'property_bedrooms',
        'property_lp_no',
        'property_year',
        'property_plot_no',
        'property_ownership_type',
        'property_bathrooms',
        'property_parking',
        'property_address',
        'property_locality',
        'property_city',
        'property_state',
        'property_pincode',
        'property_latitude',
        'property_longitude',
        'property_contact_name',
        'property_contact_phone',
        'property_contact_type',
        'property_source',
        'property_status',
        'property_created_by_type',
        'property_created_by_id',
        'property_created_by',
        'property_reviewed_by',
        'property_assigned_user_id',
        'property_published_at',
        'property_other_service_name',
        'property_approval_authority',
        'property_village',
        'property_nearby_location',
        'property_custom_nearby_location',
        'property_district',
        'property_mandal',
        'property_panchayati',
        'property_gvmc',
        'property_vmrda',
        'property_registration_area',
        'property_authority',
        'property_residential_type',
        'property_commercial_type',
        'property_development_type',
        'property_layout_type',
        'property_construction_status',
        'property_construction_type',
        'property_price_range',
        'property_price_per_sqft',
        'property_age',
        'property_facing',
        'property_total_floors',
        'property_floor_number',
        'property_furnishing',
        'property_under',
        'property_owner_name',
        'property_owner_phone',
        'property_owner_email',
        'property_verified',
        'property_submitted_at',
        'property_is_featured',
        'property_view_count',
        'property_lead_count',
        'property_is_deleted',
        'property_review_remarks',
        'property_rejected_reason',
        'property_approved_at',
        'property_approved_by_user_id',
        'property_rejected_at',
        'property_rejected_by_user_id',
        'property_archived_reason',
        'property_archived_at',
        'property_archived_by_user_id',
        'property_restored_at',
        'property_restored_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'property_type' => PropertyType::class,
            'property_listing_type' => PropertyListingType::class,
            'property_contact_type' => PropertyContactType::class,
            'property_created_by_type' => PropertyCreatedByType::class,
            'property_source' => PropertySource::class,
            'property_record_type' => PropertyRecordType::class,
            'property_status' => PropertyStatus::class,
            'property_price' => 'decimal:2',
            'property_negotiable' => 'boolean',
            'property_area_sqft' => 'decimal:2',
            'property_area' => 'decimal:2',
            'property_bedrooms' => 'integer',
            'property_year' => 'integer',
            'property_ownership_type' => PropertyOwnershipType::class,
            'property_bathrooms' => 'integer',
            'property_parking' => 'integer',
            'property_latitude' => 'decimal:7',
            'property_longitude' => 'decimal:7',
            'property_published_at' => 'datetime',
            'property_total_floors' => 'integer',
            'property_verified' => 'boolean',
            'property_submitted_at' => 'datetime',
            'property_is_featured' => 'boolean',
            'property_view_count' => 'integer',
            'property_lead_count' => 'integer',
            'property_is_deleted' => 'boolean',
            'property_approved_at' => 'datetime',
            'property_rejected_at' => 'datetime',
            'property_archived_at' => 'datetime',
            'property_restored_at' => 'datetime',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class, 'property_id', 'property_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PropertyDocument::class, 'property_id', 'property_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(PropertyReview::class, 'property_id', 'property_id');
    }

    public function reviewLogs(): HasMany
    {
        return $this->hasMany(PropertyReviewLog::class, 'property_id', 'property_id');
    }

    public function duplicateMatches(): HasMany
    {
        return $this->hasMany(PropertyDuplicateMatch::class, 'property_id', 'property_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'property_created_by', 'user_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'property_reviewed_by', 'user_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'property_assigned_user_id', 'user_id');
    }

    public function parentProperty(): BelongsTo
    {
        return $this->belongsTo(self::class, 'property_parent_property_id', 'property_id');
    }

    public function vizaglandCopy(): HasOne
    {
        return $this->hasOne(self::class, 'property_parent_property_id', 'property_id')
            ->where('property_record_type', PropertyRecordType::VizaglandCopy);
    }

    public function isOriginal(): bool
    {
        return $this->property_record_type === PropertyRecordType::Original;
    }

    public function isVizaglandCopy(): bool
    {
        return $this->property_record_type === PropertyRecordType::VizaglandCopy;
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'property_approved_by_user_id', 'user_id');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'property_rejected_by_user_id', 'user_id');
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'property_archived_by_user_id', 'user_id');
    }

    public function restoredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'property_restored_by_user_id', 'user_id');
    }
}
