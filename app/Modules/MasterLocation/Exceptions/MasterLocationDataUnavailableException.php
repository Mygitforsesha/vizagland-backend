<?php

namespace App\Modules\MasterLocation\Exceptions;

use RuntimeException;

class MasterLocationDataUnavailableException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Master location data is not available yet. Please run: php artisan master-locations:setup');
    }
}
