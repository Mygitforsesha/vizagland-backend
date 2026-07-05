<?php

namespace App\Modules\Property\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyContactNumber extends Model
{
    protected $primaryKey = 'property_contact_number_id';

    protected $table = 'property_contact_numbers';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'property_id',
        'property_contact_number_registration_type',
        'property_contact_number_phone_number',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }
}
