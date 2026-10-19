<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMedicationCategoryRequest extends FormRequest
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
            'medication_category_code' =>   ['required', 'string', 'size:1', 'unique:medication_categories,medication_category_code'],
            'medication_category_name' =>   ['required', 'string', 'max:30'],
        ];
    }

    public function messages(): array
    {
        return[
            'medication_category_code.size' => 'Code maksimal 1 karakter',
            'medication_category_code.unique' => 'Tidak boleh ada code kategori yang sama',
        ];
    }
}
