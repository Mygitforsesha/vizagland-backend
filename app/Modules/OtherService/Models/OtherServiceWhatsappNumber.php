<?php

namespace App\Modules\OtherService\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtherServiceWhatsappNumber extends Model
{
    public const CREATED_AT = 'other_service_whatsapp_number_created_at';

    public const UPDATED_AT = 'other_service_whatsapp_number_updated_at';

    protected $primaryKey = 'other_service_whatsapp_number_id';

    protected $table = 'other_service_whatsapp_numbers';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'other_service_id',
        'other_service_whatsapp_number',
        'other_service_whatsapp_number_is_primary',
        'other_service_whatsapp_number_sort_order',
    ];

    protected function casts(): array
    {
        return [
            'other_service_whatsapp_number_is_primary' => 'boolean',
            'other_service_whatsapp_number_sort_order' => 'integer',
        ];
    }

    public function otherService(): BelongsTo
    {
        return $this->belongsTo(OtherService::class, 'other_service_id', 'other_service_id');
    }
}
