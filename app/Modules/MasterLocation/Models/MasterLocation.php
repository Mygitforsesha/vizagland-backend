<?php

namespace App\Modules\MasterLocation\Models;

use Illuminate\Database\Eloquent\Model;

class MasterLocation extends Model
{
    public const CREATED_AT = 'master_location_created_at';

    public const UPDATED_AT = 'master_location_updated_at';

    protected $primaryKey = 'master_location_id';

    protected $table = 'master_locations';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'master_location_village',
        'master_location_nearby_location',
        'master_location_additional_nearby_location',
        'master_location_district',
        'master_location_mandal',
        'master_location_panchayati',
        'master_location_gvmc_zone',
        'master_location_gvmc_ward',
        'master_location_vmrda',
        'master_location_registration_office',
        'master_location_authority',
    ];

    protected function casts(): array
    {
        return [
            'master_location_created_at' => 'datetime',
            'master_location_updated_at' => 'datetime',
        ];
    }
}
