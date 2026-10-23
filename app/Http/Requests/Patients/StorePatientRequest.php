<?php

namespace App\Http\Requests\Patients;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientRequest extends FormRequest
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
            'nik'           => ['digits:16','unique:patients,nik'],
            'patient_name'  => ['required', 'string', 'max:50'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'gender'        => ['required', Rule::in(['male', 'female'])],
            'address'       => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'nik.unique'    => ['nik sudah terdaftar.'],
        ];
    }
}
