<?php

namespace App\Modules\OtherService\Models;

use Illuminate\Database\Eloquent\Model;

class OtherService extends Model
{
    public const CREATED_AT = 'other_service_created_at';

    public const UPDATED_AT = 'other_service_updated_at';

    protected $primaryKey = 'other_service_id';

    protected $table = 'other_services';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'other_service_name',
        'other_service_slug',
        'other_service_description',
        'other_service_icon',
        'other_service_sort_order',
        'other_service_is_active',
    ];

    protected function casts(): array
    {
        return [
            'other_service_sort_order' => 'integer',
            'other_service_is_active' => 'boolean',
        ];
    }
}
