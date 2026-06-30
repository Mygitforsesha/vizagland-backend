<?php

namespace App\Modules\User\Models;

use App\Modules\User\Enums\UserGender;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    protected $primaryKey = 'user_profile_id';

    protected $table = 'user_profiles';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'user_dob',
        'user_gender',
        'user_latitude',
        'user_longitude',
        'user_road',
        'user_colony',
        'user_suburb',
        'user_village',
        'user_nearby_location',
        'user_custom_nearby_location',
        'user_district',
        'user_mandal',
        'user_panchayati',
        'user_gvmc_zone_ward_number',
        'user_vmrda',
        'user_registration_area',
        'user_gvmc_vmrda',
        'user_state',
        'user_pincode',
        'user_country',
        'user_authority',
    ];

    protected function casts(): array
    {
        return [
            'user_dob' => 'date',
            'user_gender' => UserGender::class,
            'user_latitude' => 'decimal:7',
            'user_longitude' => 'decimal:7',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
