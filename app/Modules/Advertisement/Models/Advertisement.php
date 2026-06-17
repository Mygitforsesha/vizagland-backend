<?php

namespace App\Modules\Advertisement\Models;

use App\Modules\Advertisement\Enums\AdvertisementType;
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
        'advertisement_image_path',
        'advertisement_redirect_url',
        'advertisement_display_order',
        'advertisement_start_date',
        'advertisement_end_date',
        'advertisement_is_active',
        'advertisement_created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'advertisement_type' => AdvertisementType::class,
            'advertisement_display_order' => 'integer',
            'advertisement_start_date' => 'date',
            'advertisement_end_date' => 'date',
            'advertisement_is_active' => 'boolean',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advertisement_created_by_user_id', 'user_id');
    }
}
