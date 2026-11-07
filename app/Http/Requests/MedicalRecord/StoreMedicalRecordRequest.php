<?php

namespace App\Http\Requests\MedicalRecord;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalRecordRequest extends FormRequest
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
        return [
            'queue_id' => ['required', 'exists:queues,queue_id'],
            'patient_id' => ['required', 'exists:patients,patient_id'],
            'main_complaint' => 'required',
            'blood_pressure' => 'nullable',
            'temperature' => ['nullable', 'max:5'],
            'weight' => 'nullable',
            'diagnosis' => 'required',
            'notes_on_treatment' => 'nullable',

            'prescriptions' => ['nullable', 'array'],
            'prescriptions.*.medication_id' => ['required', 'exists:medications,medication_id'],
            'prescriptions.*.qty' => ['required', 'integer', 'min:1'],
            'prescriptions.*.dosage' => ['required', 'string', 'max:100'],
        ];
    }
}
