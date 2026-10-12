<?php

namespace App\Modules\OtherService\Requests\Concerns;

use Illuminate\Validation\Validator;

trait ValidatesOtherServiceWhatsappNumbers
{
    protected function prepareWhatsappNumbers(): void
    {
        $numbers = $this->input('other_service_whatsapp_numbers');

        if (! is_array($numbers)) {
            return;
        }

        $normalized = [];

        foreach ($numbers as $item) {
            if (! is_array($item)) {
                $normalized[] = $item;

                continue;
            }

            if (isset($item['other_service_whatsapp_number']) && is_string($item['other_service_whatsapp_number'])) {
                $number = preg_replace('/[\s\-]/', '', trim($item['other_service_whatsapp_number'])) ?? '';
                $item['other_service_whatsapp_number'] = $number === '' ? null : $number;
            }

            if (
                array_key_exists('other_service_whatsapp_number_is_primary', $item)
                && is_string($item['other_service_whatsapp_number_is_primary'])
            ) {
                $item['other_service_whatsapp_number_is_primary'] = filter_var(
                    $item['other_service_whatsapp_number_is_primary'],
                    FILTER_VALIDATE_BOOLEAN,
                );
            }

            $normalized[] = $item;
        }

        $this->merge(['other_service_whatsapp_numbers' => $normalized]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function whatsappNumberRules(): array
    {
        return [
            'other_service_whatsapp_numbers' => ['nullable', 'array', 'max:10'],
            'other_service_whatsapp_numbers.*.other_service_whatsapp_number' => [
                'required',
                'string',
                'regex:/^\+?[0-9]{10,15}$/',
                'distinct',
            ],
            'other_service_whatsapp_numbers.*.other_service_whatsapp_number_is_primary' => ['required', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $numbers = $this->input('other_service_whatsapp_numbers');

            if (! is_array($numbers) || $numbers === []) {
                return;
            }

            $primaryCount = 0;

            foreach ($numbers as $item) {
                if (! is_array($item)) {
                    continue;
                }

                if (filter_var($item['other_service_whatsapp_number_is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                    $primaryCount++;
                }
            }

            if ($primaryCount !== 1) {
                $validator->errors()->add(
                    'other_service_whatsapp_numbers',
                    'Exactly one WhatsApp number must be marked as primary.',
                );
            }
        });
    }

    /**
     * Null means the client did not send the field, so existing numbers stay unchanged.
     *
     * @return list<array{other_service_whatsapp_number: string, other_service_whatsapp_number_is_primary: bool, other_service_whatsapp_number_sort_order: int}>|null
     */
    public function whatsappNumbers(): ?array
    {
        if (! $this->exists('other_service_whatsapp_numbers')) {
            return null;
        }

        $numbers = $this->input('other_service_whatsapp_numbers');

        if (! is_array($numbers)) {
            return [];
        }

        $normalized = [];

        foreach (array_values($numbers) as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $normalized[] = [
                'other_service_whatsapp_number' => (string) $item['other_service_whatsapp_number'],
                'other_service_whatsapp_number_is_primary' => (bool) $item['other_service_whatsapp_number_is_primary'],
                'other_service_whatsapp_number_sort_order' => $index,
            ];
        }

        return $normalized;
    }
}
