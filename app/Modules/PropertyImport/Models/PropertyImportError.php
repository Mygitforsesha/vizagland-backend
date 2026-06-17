<?php

namespace App\Modules\PropertyImport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyImportError extends Model
{
    public const CREATED_AT = 'property_import_error_created_at';

    public const UPDATED_AT = null;

    protected $primaryKey = 'property_import_error_id';

    protected $table = 'property_import_errors';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'property_import_job_id',
        'property_import_row_number',
        'property_import_error_message',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'property_import_row_number' => 'integer',
            'property_import_error_created_at' => 'datetime',
        ];
    }

    public function importJob(): BelongsTo
    {
        return $this->belongsTo(PropertyImportJob::class, 'property_import_job_id', 'property_import_job_id');
    }
}
