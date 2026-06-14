<?php

namespace App\Modules\Report\Models;

use App\Modules\Report\Enums\ExportFormat;
use App\Modules\Report\Enums\ExportJobStatus;
use App\Modules\Report\Enums\ExportType;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportJob extends Model
{
    public const CREATED_AT = 'export_job_created_at';

    public const UPDATED_AT = null;

    protected $primaryKey = 'export_job_id';

    protected $table = 'export_jobs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'export_job_user_id',
        'export_job_type',
        'export_job_format',
        'export_job_status',
        'export_job_file_name',
        'export_job_file_path',
        'export_job_file_size',
        'export_job_filters',
        'export_job_error_message',
        'export_job_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'export_job_type' => ExportType::class,
            'export_job_format' => ExportFormat::class,
            'export_job_status' => ExportJobStatus::class,
            'export_job_filters' => 'array',
            'export_job_file_size' => 'integer',
            'export_job_created_at' => 'datetime',
            'export_job_completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'export_job_user_id', 'user_id');
    }
}
