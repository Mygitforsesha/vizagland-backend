<?php

namespace App\Modules\PropertyImport\Models;

use App\Modules\PropertyImport\Enums\PropertyImportJobStatus;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PropertyImportJob extends Model
{
    protected $primaryKey = 'property_import_job_id';

    protected $table = 'property_import_jobs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'property_import_file_name',
        'property_import_file_path',
        'property_import_total_rows',
        'property_import_success_rows',
        'property_import_failed_rows',
        'property_import_status',
        'property_import_created_by_user_id',
        'property_import_started_at',
        'property_import_completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'property_import_total_rows' => 'integer',
            'property_import_success_rows' => 'integer',
            'property_import_failed_rows' => 'integer',
            'property_import_status' => PropertyImportJobStatus::class,
            'property_import_started_at' => 'datetime',
            'property_import_completed_at' => 'datetime',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'property_import_created_by_user_id', 'user_id');
    }

    public function errors(): HasMany
    {
        return $this->hasMany(PropertyImportError::class, 'property_import_job_id', 'property_import_job_id');
    }
}
