<?php

namespace App\Http\Requests;

use App\Models\MedicationCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMedicationCategoryRequest extends FormRequest
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
        $category = $this->route('medication_category');
        $categoryId = $category instanceof MedicationCategory ? $category->medication_category_id : $category;
        return [
            'medication_category_code' => [
                'required',
                'string',
                'size:1',
                Rule::unique('medication_categories', 'medication_category_code')
                ->ignore($categoryId, 'medication_category_id'),
            ],
            'medication_category_name' => ['required', 'string', 'max:30'],
        ];
    }

    public function messages(): array
    {
        return [
            'medication_category_code.size' => 'Code maksimal 1 karakter'
        ];
    }
}
