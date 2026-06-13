<?php

namespace App\Modules\Property\Models;

use App\Modules\Property\Enums\PropertyAreaUnit;
use App\Modules\Property\Enums\PropertyContactType;
use App\Modules\Property\Enums\PropertyCreatedByType;
use App\Modules\Property\Enums\PropertyListingType;
use App\Modules\Property\Enums\PropertyOwnershipType;
use App\Modules\Property\Enums\PropertySource;
use App\Modules\Property\Enums\PropertyStatus;
use App\Modules\Property\Enums\PropertyType;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    protected $primaryKey = 'property_id';

    protected $table = 'properties';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'property_code',
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
        'property_lp_number',
        'property_year',
        'property_plot_number',
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
        'property_assigned_to',
        'property_published_at',
    ];

    protected function casts(): array
    {
        return [
            'property_type' => PropertyType::class,
            'property_listing_type' => PropertyListingType::class,
            'property_contact_type' => PropertyContactType::class,
            'property_created_by_type' => PropertyCreatedByType::class,
            'property_source' => PropertySource::class,
            'property_status' => PropertyStatus::class,
            'property_price' => 'decimal:2',
            'property_negotiable' => 'boolean',
            'property_area_sqft' => 'decimal:2',
            'property_area' => 'decimal:2',
            'property_area_unit' => PropertyAreaUnit::class,
            'property_bedrooms' => 'integer',
            'property_year' => 'integer',
            'property_ownership_type' => PropertyOwnershipType::class,
            'property_bathrooms' => 'integer',
            'property_parking' => 'integer',
            'property_latitude' => 'decimal:7',
            'property_longitude' => 'decimal:7',
            'property_published_at' => 'datetime',
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
        return $this->belongsTo(User::class, 'property_assigned_to', 'user_id');
    }
}
