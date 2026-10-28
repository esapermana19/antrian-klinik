<?php

namespace App\Http\Requests\Queues;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreQueueRequest extends FormRequest
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
            // Validasi Data Jadwal & Slot
            'schedule_id' => ['required', 'exists:practice_schedules,schedule_id'],
            'queue_date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'string'],
            'queue_number' => ['required', 'string', 'size:10'],

            'patient_id' => ['nullable', 'exists:patients,patient_id'],

            // Validasi Data Pasien (Form Input Langsung)
            'nik' => ['required_without:patient_id', 'string', 'digits:16'],
            'patient_name' => ['required_without:patient_id', 'string', 'max:50'],
            'date_of_birth' => ['required_without:patient_id', 'date', 'before_or_equal:today'],
            'gender' => ['required_without:patient_id', 'in:male,female'],
            'address' => ['required_without:patient_id'],
        ];
    }
}
