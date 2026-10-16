<?php

namespace App\Http\Requests;

use App\Models\PracticeSchedule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePracticeScheduleRequest extends FormRequest
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
        $schedule = $this->route('practice_schedule');
        $scheduleId = $schedule instanceof PracticeSchedule ? $schedule->schedule_id : $schedule;

        return [
            'day' => [
                'required',
                Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']),
            ],
            'session' => [
                'required',
                Rule::in(['morning', 'afternoon']),
                Rule::unique('practice_schedules')->where(function ($query) {
                    return $query->where('day', $this->day);
                })->ignore($scheduleId, 'schedule_id'),
            ],
            'opening_hours' => ['required', 'date_format:H:i'],
            'closing_hours' => ['required', 'date_format:H:i', 'after:opening_hours'],
            'max_quota' => ['required', 'integer', 'min:1', 'max:20'],
            'status' => ['required', Rule::in(['open', 'closed'])],
        ];
    }

    public function messages(): array
    {
        return [
            'session.unique' => 'Jadwal hari dan sesi ini sudah terdaftar.',
            'closing_hours.after' => 'Jam tutup harus lebih besar dari jam buka',
        ];
    }
}
