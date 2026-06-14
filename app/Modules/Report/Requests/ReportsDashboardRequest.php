<?php

namespace App\Modules\Report\Requests;

use App\Modules\Report\Enums\ExportFormat;
use App\Modules\Report\Enums\ExportType;
use App\Modules\Report\Enums\ReportPeriod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportsDashboardRequest extends FormRequest
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
            'period' => ['nullable', Rule::enum(ReportPeriod::class)],
        ];
    }

    public function period(): ReportPeriod
    {
        $period = $this->input('period');

        return $period !== null
            ? ReportPeriod::from($period)
            : ReportPeriod::Daily;
    }
}
