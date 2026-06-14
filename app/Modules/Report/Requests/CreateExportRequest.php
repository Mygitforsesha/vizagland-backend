<?php

namespace App\Modules\Report\Requests;

use App\Modules\Report\Enums\ExportFormat;
use App\Modules\Report\Enums\ExportType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'export_type' => ['required', Rule::enum(ExportType::class)],
            'export_format' => ['required', Rule::enum(ExportFormat::class)],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }

    public function exportType(): ExportType
    {
        return ExportType::from($this->input('export_type'));
    }

    public function exportFormat(): ExportFormat
    {
        return ExportFormat::from($this->input('export_format'));
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return $this->only(['date_from', 'date_to']);
    }
}
