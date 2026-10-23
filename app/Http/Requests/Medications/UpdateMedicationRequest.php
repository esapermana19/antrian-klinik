<?php

namespace App\Http\Requests\Medications;

use App\Models\Medication;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $medication = $this->route('medications');
        $medicationId = $medication instanceof Medication ? $medication->medication_id : $medication;

        return [
            'medication_category_id' => ['required', 'exists:medication_categories,medication_category_id'],
            'medication_name' => ['required', 'string', 'max:50'],
            'stock' => ['required', 'numeric', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'medication_name.max' => 'Maksimal nama obat 50 karakter.',
        ];
    }
}
