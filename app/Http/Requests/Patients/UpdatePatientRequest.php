<?php

namespace App\Http\Requests\Patients;

use App\Models\Patient;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends FormRequest
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
        $patient = $this->route('patient');
        $patientId = $patient instanceof Patient ? $patient->patient_id : $patient;

        return [
            'nik' => [
                'size:16',
                Rule::unique('patients', 'nik')->ignore($patientId, 'patient_id'),
            ],
            'patient_name' => ['required', 'string', 'max:50'],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'address' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'nik.unique' => 'nik sudah terdaftar.',
        ];
    }
}
