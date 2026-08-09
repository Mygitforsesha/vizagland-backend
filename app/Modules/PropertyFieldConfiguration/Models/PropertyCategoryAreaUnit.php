<?php

namespace App\Modules\PropertyFieldConfiguration\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyCategoryAreaUnit extends Model
{
    protected $primaryKey = 'property_category_area_unit_id';

    protected $table = 'property_category_area_units';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'property_category_value',
        'property_area_unit_value',
        'property_area_unit_label',
        'property_category_area_unit_is_active',
        'property_category_area_unit_display_order',
    ];

    protected function casts(): array
    {
        return [
            'property_category_area_unit_is_active' => 'boolean',
            'property_category_area_unit_display_order' => 'integer',
        ];
    }
}
