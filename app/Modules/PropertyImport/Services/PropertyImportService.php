<?php

namespace App\Modules\PropertyImport\Services;

use App\Modules\ActivityLog\Enums\ActivityLogType;
use App\Modules\ActivityLog\Services\ActivityLogService;
use App\Modules\Property\Services\PropertyService;
use App\Modules\PropertyImport\Enums\PropertyImportJobStatus;
use App\Modules\PropertyImport\Imports\PropertyImportColumnMapping;
use App\Modules\PropertyImport\Imports\PropertyImportRowValidator;
use App\Modules\PropertyImport\Imports\Readers\PropertyImportSpreadsheetReader;
use App\Modules\PropertyImport\Jobs\ProcessPropertyImportJob;
use App\Modules\PropertyImport\Models\PropertyImportJob;
use App\Modules\PropertyImport\Repositories\PropertyImportErrorRepository;
use App\Modules\PropertyImport\Repositories\PropertyImportJobRepository;
use App\Modules\Report\Exports\Writers\XlsxExportWriter;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class PropertyImportService
{
    public function __construct(
        private readonly PropertyImportJobRepository $propertyImportJobRepository,
        private readonly PropertyImportErrorRepository $propertyImportErrorRepository,
        private readonly PropertyImportSpreadsheetReader $propertyImportSpreadsheetReader,
        private readonly PropertyImportRowValidator $propertyImportRowValidator,
        private readonly PropertyService $propertyService,
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function upload(UploadedFile $file, User $user): PropertyImportJob
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, PropertyImportColumnMapping::allowedExtensions(), true)) {
            throw new RuntimeException('Import file must be xlsx, xls, or csv.');
        }

        $importJob = $this->propertyImportJobRepository->create([
            'property_import_file_name' => $file->getClientOriginalName(),
            'property_import_file_path' => '',
            'property_import_status' => PropertyImportJobStatus::Pending,
            'property_import_created_by_user_id' => $user->user_id,
        ]);

        $storedPath = $file->storeAs(
            'property-imports/'.$importJob->property_import_job_id,
            Str::uuid().'.'.$extension,
            'local',
        );

        return $this->propertyImportJobRepository->update($importJob, [
            'property_import_file_path' => $storedPath,
        ]);
    }

    public function dispatch(PropertyImportJob $importJob): void
    {
        ProcessPropertyImportJob::dispatch(
            $importJob->property_import_job_id,
        );
    }

    public function list(int $perPage): LengthAwarePaginator
    {
        return $this->propertyImportJobRepository->paginate($perPage);
    }

    public function show(int $propertyImportJobId): PropertyImportJob
    {
        $importJob = $this->propertyImportJobRepository->findByIdWithRelations($propertyImportJobId);

        if ($importJob === null) {
            throw (new ModelNotFoundException)->setModel(PropertyImportJob::class, [$propertyImportJobId]);
        }

        return $importJob;
    }

    public function process(int $propertyImportJobId): void
    {
        $importJob = $this->propertyImportJobRepository->findById($propertyImportJobId);

        if ($importJob === null) {
            throw (new ModelNotFoundException)->setModel(PropertyImportJob::class, [$propertyImportJobId]);
        }

        $this->propertyImportJobRepository->update($importJob, [
            'property_import_status' => PropertyImportJobStatus::Processing,
            'property_import_started_at' => now(),
        ]);

        try {
            $absolutePath = Storage::disk('local')->path($importJob->property_import_file_path);

            if (! is_file($absolutePath)) {
                throw new RuntimeException('Import file no longer exists.');
            }

            $extension = pathinfo($importJob->property_import_file_name, PATHINFO_EXTENSION);
            $sheet = $this->propertyImportSpreadsheetReader->read($absolutePath, $extension);
            $headers = $sheet['headers'];
            $rows = $sheet['rows'];

            $createdBy = $importJob->createdBy()->first();

            if ($createdBy === null) {
                throw new RuntimeException('Import job creator could not be resolved.');
            }

            $successCount = 0;
            $failedCount = 0;
            $errorBuffer = [];

            foreach ($rows as $row) {
                $rowNumber = $row['row_number'];
                $attributes = $this->mapRowToAttributes($headers, $row['values']);
                $attributes = $this->coerceAttributeTypes($attributes);

                try {
                    $validated = $this->propertyImportRowValidator->validate($attributes);
                    $this->propertyService->createFromBulkImport($validated, $createdBy);
                    $successCount++;
                } catch (ValidationException $exception) {
                    $failedCount++;
                    $errorBuffer[] = $this->buildErrorRecord(
                        $importJob->property_import_job_id,
                        $rowNumber,
                        $this->formatValidationErrors($exception),
                    );
                } catch (Throwable $exception) {
                    $failedCount++;
                    $errorBuffer[] = $this->buildErrorRecord(
                        $importJob->property_import_job_id,
                        $rowNumber,
                        $exception->getMessage(),
                    );
                }

                if (count($errorBuffer) >= 100) {
                    $this->propertyImportErrorRepository->insertMany($errorBuffer);
                    $errorBuffer = [];
                }
            }

            if ($errorBuffer !== []) {
                $this->propertyImportErrorRepository->insertMany($errorBuffer);
            }

            $totalRows = count($rows);
            $status = $this->resolveFinalStatus($totalRows, $successCount, $failedCount);

            $completedJob = $this->propertyImportJobRepository->update($importJob, [
                'property_import_total_rows' => $totalRows,
                'property_import_success_rows' => $successCount,
                'property_import_failed_rows' => $failedCount,
                'property_import_status' => $status,
                'property_import_completed_at' => now(),
            ]);

            $this->activityLogService->log(
                type: ActivityLogType::Property,
                action: 'bulk_imported',
                description: "Bulk property import completed: {$successCount} succeeded, {$failedCount} failed",
                entityType: 'property_import_job',
                entityId: $completedJob->property_import_job_id,
                user: $createdBy,
                metadata: [
                    'property_import_file_name' => $completedJob->property_import_file_name,
                    'property_import_total_rows' => $totalRows,
                    'property_import_success_rows' => $successCount,
                    'property_import_failed_rows' => $failedCount,
                    'property_import_status' => $status->value,
                ],
            );
        } catch (Throwable $exception) {
            $this->propertyImportJobRepository->update($importJob, [
                'property_import_status' => PropertyImportJobStatus::Failed,
                'property_import_completed_at' => now(),
            ]);

            $this->propertyImportErrorRepository->insertMany([
                $this->buildErrorRecord(
                    $importJob->property_import_job_id,
                    0,
                    $exception->getMessage(),
                ),
            ]);

            throw $exception;
        }
    }

    /**
     * @return array{absolute_path: string, file_name: string}
     */
    public function generateTemplateFile(): array
    {
        $columns = PropertyImportColumnMapping::columns();
        $sample = PropertyImportColumnMapping::sampleRow();
        $row = [];

        foreach ($columns as $column) {
            $row[] = $sample[$column] ?? '';
        }

        $fileName = 'property-import-template.xlsx';
        $relativePath = 'property-imports/templates/'.$fileName;
        $absolutePath = Storage::disk('local')->path($relativePath);

        Storage::disk('local')->makeDirectory('property-imports/templates');

        app(XlsxExportWriter::class)->write($absolutePath, $columns, [$row]);

        return [
            'absolute_path' => $absolutePath,
            'file_name' => $fileName,
        ];
    }

    /**
     * @param  list<string>  $headers
     * @param  list<scalar|null>  $values
     * @return array<string, mixed>
     */
    private function mapRowToAttributes(array $headers, array $values): array
    {
        $attributes = [];

        foreach ($headers as $index => $header) {
            $value = $values[$index] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            $attributes[$header] = $value;
        }

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function coerceAttributeTypes(array $attributes): array
    {
        foreach (['property_total_floors', 'property_bedrooms', 'property_year'] as $field) {
            if (isset($attributes[$field]) && is_numeric($attributes[$field])) {
                $attributes[$field] = (int) $attributes[$field];
            }
        }

        foreach (['property_price', 'property_area'] as $field) {
            if (isset($attributes[$field]) && is_numeric($attributes[$field])) {
                $attributes[$field] = (float) $attributes[$field];
            }
        }

        return $attributes;
    }

    /**
     * @return array{property_import_job_id: int, property_import_row_number: int, property_import_error_message: string}
     */
    private function buildErrorRecord(int $importJobId, int $rowNumber, string $message): array
    {
        return [
            'property_import_job_id' => $importJobId,
            'property_import_row_number' => $rowNumber,
            'property_import_error_message' => $message,
        ];
    }

    private function formatValidationErrors(ValidationException $exception): string
    {
        $messages = [];

        foreach ($exception->errors() as $field => $fieldMessages) {
            foreach ($fieldMessages as $fieldMessage) {
                $messages[] = "{$field}: {$fieldMessage}";
            }
        }

        return implode(' | ', $messages);
    }

    private function resolveFinalStatus(int $totalRows, int $successCount, int $failedCount): PropertyImportJobStatus
    {
        if ($totalRows === 0) {
            return PropertyImportJobStatus::Failed;
        }

        if ($failedCount === 0) {
            return PropertyImportJobStatus::Completed;
        }

        if ($successCount === 0) {
            return PropertyImportJobStatus::Failed;
        }

        return PropertyImportJobStatus::Partial;
    }
}
