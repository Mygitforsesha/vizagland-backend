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
        'property_field_placeholder',
        'property_field_section',
        'property_field_data_type',
        'property_field_is_active',
        'property_field_is_required',
        'property_field_is_readonly',
        'property_field_is_searchable',
        'property_field_is_multiple',
        'property_field_options',
        'property_field_options_api',
        'property_field_validation',
        'property_field_default_value',
        'property_field_depends_on',
        'property_field_public_section',
        'property_field_public_order',
        'property_field_display_order',
    ];

    protected function casts(): array
    {
        return [
            'property_field_data_type' => PropertyFieldDataType::class,
            'property_field_is_active' => 'boolean',
            'property_field_is_required' => 'boolean',
            'property_field_is_readonly' => 'boolean',
            'property_field_is_searchable' => 'boolean',
            'property_field_is_multiple' => 'boolean',
            'property_field_options' => 'array',
            'property_field_validation' => 'array',
            'property_field_depends_on' => 'array',
            'property_field_display_order' => 'integer',
            'property_field_public_order' => 'integer',
            'property_field_created_at' => 'datetime',
            'property_field_updated_at' => 'datetime',
        ];
    }
}
