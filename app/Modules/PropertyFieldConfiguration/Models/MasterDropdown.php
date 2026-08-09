<?php

namespace App\Modules\PropertyFieldConfiguration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterDropdown extends Model
{
    protected $primaryKey = 'master_dropdown_id';

    protected $table = 'master_dropdowns';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'master_dropdown_key',
        'master_dropdown_label',
        'master_dropdown_is_active',
        'master_dropdown_display_order',
    ];

    protected function casts(): array
    {
        return [
            'master_dropdown_is_active' => 'boolean',
            'master_dropdown_display_order' => 'integer',
        ];
    }

    public function options(): HasMany
    {
        return $this->hasMany(MasterDropdownOption::class, 'master_dropdown_id', 'master_dropdown_id');
    }
}
