<?php

namespace App\Modules\Property\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyImage extends Model
{
    protected $primaryKey = 'property_image_id';

    protected $table = 'property_images';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'property_id',
        'property_image_path',
        'property_image_name',
        'property_image_size',
        'property_image_sort_order',
    ];

    protected function casts(): array
    {
        return [
            'property_image_size' => 'integer',
            'property_image_sort_order' => 'integer',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }
}
