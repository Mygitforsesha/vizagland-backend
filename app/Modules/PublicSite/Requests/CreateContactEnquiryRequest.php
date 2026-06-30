<?php

namespace App\Modules\PublicSite\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateContactEnquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach ($this->optionalFields() as $field) {
            $value = $this->input($field);

            if ($value === null) {
                continue;
            }

            if (is_string($value) && trim($value) === '') {
                $normalized[$field] = null;
            }
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }

    /**
     * @return list<string>
     */
    private function optionalFields(): array
    {
        return [
            'contact_enquiry_property_reference_id',
            'contact_enquiry_district',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'contact_enquiry_full_name' => ['required', 'string', 'max:255'],
            'contact_enquiry_phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
            'contact_enquiry_email' => ['required', 'email', 'max:255'],
            'contact_enquiry_subject' => ['required', 'string', 'max:100'],
            'contact_enquiry_property_reference_id' => ['sometimes', 'nullable', 'string', 'max:50'],
            'contact_enquiry_district' => ['sometimes', 'nullable', 'string', 'max:100'],
            'contact_enquiry_message' => ['required', 'string', 'max:5000'],
            'contact_enquiry_consent' => ['required', 'accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'contact_enquiry_phone.regex' => 'Please provide a valid 10-digit mobile number.',
            'contact_enquiry_consent.accepted' => 'You must consent to being contacted regarding your enquiry.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function contactEnquiryAttributes(): array
    {
        $attributes = [
            ...$this->only([
                'contact_enquiry_full_name',
                'contact_enquiry_phone',
                'contact_enquiry_email',
                'contact_enquiry_subject',
                'contact_enquiry_message',
            ]),
            'contact_enquiry_consent' => $this->boolean('contact_enquiry_consent'),
        ];

        foreach ($this->optionalFields() as $field) {
            $value = $this->input($field);

            if (! is_string($value)) {
                continue;
            }

            $value = trim($value);

            if ($value !== '') {
                $attributes[$field] = $value;
            }
        }

        return $attributes;
    }
}
