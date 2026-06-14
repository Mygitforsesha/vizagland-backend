<?php

namespace App\Modules\PropertyFieldConfiguration\Models;

use App\Modules\PropertyFieldConfiguration\Enums\PropertyFieldDataType;
use Illuminate\Database\Eloquent\Model;

class PropertyFieldConfiguration extends Model
{
    public const CREATED_AT = 'property_field_created_at';

    public const UPDATED_AT = 'property_field_updated_at';

    protected $primaryKey = 'property_field_configuration_id';

    protected $table = 'property_field_configurations';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'property_field_key',
        'property_field_label',
        'property_field_section',
        'property_field_data_type',
        'property_field_is_active',
        'property_field_is_required',
        'property_field_display_order',
    ];

    protected function casts(): array
    {
        return [
            'property_field_data_type' => PropertyFieldDataType::class,
            'property_field_is_active' => 'boolean',
            'property_field_is_required' => 'boolean',
            'property_field_display_order' => 'integer',
            'property_field_created_at' => 'datetime',
            'property_field_updated_at' => 'datetime',
        ];
    }
}
