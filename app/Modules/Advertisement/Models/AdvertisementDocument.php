<?php

namespace App\Modules\Advertisement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvertisementDocument extends Model
{
    protected $primaryKey = 'advertisement_document_id';

    protected $table = 'advertisement_documents';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'advertisement_id',
        'advertisement_document_path',
        'advertisement_document_original_name',
        'advertisement_document_mime_type',
        'advertisement_document_size',
    ];

    protected function casts(): array
    {
        return [
            'advertisement_document_size' => 'integer',
        ];
    }

    public function advertisement(): BelongsTo
    {
        return $this->belongsTo(Advertisement::class, 'advertisement_id', 'advertisement_id');
    }
}
