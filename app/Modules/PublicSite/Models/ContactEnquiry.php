<?php

namespace App\Modules\PublicSite\Models;

use App\Modules\PublicSite\Enums\ContactEnquiryStatus;
use Illuminate\Database\Eloquent\Model;

class ContactEnquiry extends Model
{
    protected $primaryKey = 'contact_enquiry_id';

    protected $table = 'contact_enquiries';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'contact_enquiry_full_name',
        'contact_enquiry_phone',
        'contact_enquiry_email',
        'contact_enquiry_subject',
        'contact_enquiry_property_reference_id',
        'contact_enquiry_district',
        'contact_enquiry_message',
        'contact_enquiry_consent',
        'contact_enquiry_status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'contact_enquiry_consent' => 'boolean',
            'contact_enquiry_status' => ContactEnquiryStatus::class,
        ];
    }
}
