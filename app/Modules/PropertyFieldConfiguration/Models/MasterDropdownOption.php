<?php

namespace App\Modules\PropertyFieldConfiguration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterDropdownOption extends Model
{
    protected $primaryKey = 'master_dropdown_option_id';

    protected $table = 'master_dropdown_options';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'master_dropdown_id',
        'master_dropdown_option_value',
        'master_dropdown_option_label',
        'master_dropdown_option_is_active',
        'master_dropdown_option_display_order',
    ];

    protected function casts(): array
    {
        return [
            'master_dropdown_option_is_active' => 'boolean',
            'master_dropdown_option_display_order' => 'integer',
        ];
    }

    public function dropdown(): BelongsTo
    {
        return $this->belongsTo(MasterDropdown::class, 'master_dropdown_id', 'master_dropdown_id');
    }
}
