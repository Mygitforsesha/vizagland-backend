<?php

namespace App\Modules\Property\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyDocument extends Model
{
    protected $primaryKey = 'property_document_id';

    protected $table = 'property_documents';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'property_id',
        'property_document_name',
        'property_document_type',
        'property_document_path',
        'property_document_size',
    ];

    protected function casts(): array
    {
        return [
            'property_document_size' => 'integer',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }
}
