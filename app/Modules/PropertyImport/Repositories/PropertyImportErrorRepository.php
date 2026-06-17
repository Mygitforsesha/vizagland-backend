<?php

namespace App\Modules\PropertyImport\Repositories;

use App\Modules\PropertyImport\Models\PropertyImportError;

class PropertyImportErrorRepository
{
    /**
     * @param  list<array{property_import_job_id: int, property_import_row_number: int, property_import_error_message: string}>  $errors
     */
    public function insertMany(array $errors): void
    {
        if ($errors === []) {
            return;
        }

        $now = now();

        PropertyImportError::query()->insert(
            array_map(
                static fn (array $error): array => [
                    ...$error,
                    'property_import_error_created_at' => $now,
                ],
                $errors,
            ),
        );
    }
}
