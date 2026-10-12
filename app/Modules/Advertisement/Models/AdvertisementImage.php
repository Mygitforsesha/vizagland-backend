<?php

namespace App\Modules\Advertisement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvertisementImage extends Model
{
    protected $primaryKey = 'advertisement_image_id';

    protected $table = 'advertisement_images';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'advertisement_id',
        'advertisement_image_path',
        'advertisement_image_original_name',
        'advertisement_image_mime_type',
        'advertisement_image_size',
        'advertisement_image_sort_order',
    ];

    protected function casts(): array
    {
        return [
            'advertisement_image_size' => 'integer',
            'advertisement_image_sort_order' => 'integer',
        ];
    }

    public function advertisement(): BelongsTo
    {
        return $this->belongsTo(Advertisement::class, 'advertisement_id', 'advertisement_id');
    }
}
