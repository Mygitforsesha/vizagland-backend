<?php

namespace App\Modules\Property\Support;

final class DefaultPropertyOwners
{
    /**
     * @return list<array{property_owner_name: string, property_owner_phone: string, property_owner_email: string}>
     */
    public static function list(): array
    {
        return [
            [
                'property_owner_name' => 'Kanakababu',
                'property_owner_phone' => '9618170406',
                'property_owner_email' => 'admin@vizagland.com',
            ],
            [
                'property_owner_name' => 'sasi',
                'property_owner_phone' => '6039380406',
                'property_owner_email' => 'admin@vizagland.com',
            ],
        ];
    }
}
