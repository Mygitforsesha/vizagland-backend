<?php

namespace App\Modules\PublicSite\Models;

use Illuminate\Database\Eloquent\Model;

class SupportRequest extends Model
{
    protected $primaryKey = 'support_request_id';

    protected $table = 'support_requests';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'support_request_name',
        'support_request_email',
        'support_request_phone',
        'support_request_message',
        'support_request_status',
    ];
}
