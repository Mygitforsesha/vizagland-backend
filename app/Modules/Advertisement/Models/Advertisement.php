<?php

namespace App\Modules\Advertisement\Models;

use App\Modules\Advertisement\Enums\AdvertisementCategory;
use App\Modules\Advertisement\Enums\AdvertisementType;
use App\Modules\MasterLocation\Models\MasterLocation;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Advertisement extends Model
{
    public const CREATED_AT = 'advertisement_created_at';

    public const UPDATED_AT = 'advertisement_updated_at';

    protected $primaryKey = 'advertisement_id';

    protected $table = 'advertisements';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'advertisement_title',
        'advertisement_description',
        'advertisement_type',
        'advertisement_types',
        'advertisement_category',
        'advertisement_image_path',
        'advertisement_redirect_url',
        'advertisement_display_order',
        'advertisement_start_date',
        'advertisement_end_date',
        'advertisement_is_active',
        'advertisement_village_id',
        'advertisement_created_by_user_id',
        'property_category',
        'property_location',
        'property_details',
    ];

    protected function casts(): array
    {
        return [
            'advertisement_types' => 'array',
            'advertisement_category' => AdvertisementCategory::class,
            'advertisement_display_order' => 'integer',
            'advertisement_start_date' => 'date',
            'advertisement_end_date' => 'date',
            'advertisement_is_active' => 'boolean',
            'advertisement_village_id' => 'integer',
            'property_location' => 'array',
            'property_details' => 'array',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advertisement_created_by_user_id', 'user_id');
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(MasterLocation::class, 'advertisement_village_id', 'master_location_id');
    }
}
