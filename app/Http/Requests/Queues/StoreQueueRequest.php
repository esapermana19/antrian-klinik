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
            'queue_number' => ['required', 'string'],

            // Validasi Data Pasien (Form Input Langsung)
            'nik' => ['required', 'string', 'digits:16'],
            'patient_name' => ['required', 'string', 'max:50'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['required', 'in:male,female'],
            'address' => ['required'],
        ];
    }
}
