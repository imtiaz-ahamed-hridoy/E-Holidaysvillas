<?php

namespace App\Http\Requests\API;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class PropertySearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'check_in' => ['nullable', 'date_format:Y-m-d', 'required_with:check_out'],
            'check_out' => ['nullable', 'date_format:Y-m-d', 'required_with:check_in', 'after:check_in'],
            'guests' => ['nullable', 'integer', 'min:1', 'max:50'],
            'property_type_id' => ['nullable', 'integer', 'min:1', 'exists:property_types,property_type_id'],
            'min_price' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'max:99999.99', 'gte:min_price'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'check_in.required_with' => 'Check-in date is required when check-out date is provided.',
            'check_out.after' => 'Check-out date must be after check-in date.',
            'check_out.required_with' => 'Check-out date is required when check-in date is provided.',
            'guests.min' => 'Guest count must be at least 1.',
            'guests.max' => 'Guest count cannot exceed 50.',
            'property_type_id.exists' => 'The selected property type does not exist.',
            'max_price.gte' => 'Maximum price must be greater than or equal to minimum price.',
            'min_price.min' => 'Minimum price cannot be negative.',
            'max_price.min' => 'Maximum price cannot be negative.',
            'per_page.min' => 'Results per page must be at least 1.',
            'per_page.max' => 'Results per page cannot exceed 100.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'search' => $this->sanitizeString($this->input('search')),
            'location' => $this->sanitizeString($this->input('location')),
            'check_in' => $this->emptyToNull($this->input('check_in')),
            'check_out' => $this->emptyToNull($this->input('check_out')),
            'guests' => $this->emptyToNull($this->input('guests')),
            'property_type_id' => $this->emptyToNull($this->input('property_type_id')),
            'min_price' => $this->emptyToNull($this->input('min_price')),
            'max_price' => $this->emptyToNull($this->input('max_price')),
            'per_page' => $this->emptyToNull($this->input('per_page')) ?? 14,
        ]);
    }

    private function sanitizeString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim(strip_tags((string) $value));

        return $trimmed === '' ? null : $trimmed;
    }

    private function emptyToNull($value)
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $value = trim($value);

            return in_array(strtolower($value), ['', 'null'], true) ? null : $value;
        }

        return $value;
    }

    protected function failedValidation(Validator $validator): void
    {
        $response = response()->json([
            'status' => false,
            'message' => 'Validation failed',
            'code' => 422,
            'errors' => $validator->errors()->messages(),
        ], 422);

        throw new ValidationException($validator, $response);
    }
}
