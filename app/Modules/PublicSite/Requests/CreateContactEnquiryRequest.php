<?php

namespace App\Modules\PublicSite\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateContactEnquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $optionalFields = [
            'contact_enquiry_property_reference_id',
            'contact_enquiry_district',
        ];

        $normalized = [];

        foreach ($optionalFields as $field) {
            $value = $this->input($field);

            if (is_string($value) && trim($value) === '') {
                $normalized[$field] = null;
            }
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
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
            'contact_enquiry_property_reference_id' => [
                'nullable',
                'string',
                'max:50',
                Rule::exists('properties', 'property_reference_id'),
            ],
            'contact_enquiry_district' => ['nullable', 'string', 'max:100'],
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
            'contact_enquiry_property_reference_id.exists' => 'The provided property reference ID was not found.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function contactEnquiryAttributes(): array
    {
        return [
            ...$this->only([
                'contact_enquiry_full_name',
                'contact_enquiry_phone',
                'contact_enquiry_email',
                'contact_enquiry_subject',
                'contact_enquiry_property_reference_id',
                'contact_enquiry_district',
                'contact_enquiry_message',
            ]),
            'contact_enquiry_consent' => $this->boolean('contact_enquiry_consent'),
        ];
    }
}
