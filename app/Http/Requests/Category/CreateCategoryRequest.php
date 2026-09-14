<?php

namespace App\Http\Requests\Category;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:150'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Cần nhập tên danh mục',
            'name.string' => 'Tên danh mục phải là chuỗi kí tự',
            'name.max' => 'Tên danh mục không được nhiều hơn 120 kí tự',
            'slug.string' => 'Slug phải là chuỗi kí tự',
            'slug.max' => 'Slug không được nhiều hơn 150 kí tự',
            'parent_id.integer' => 'ID cha phải là số nguyên',
            'parent_id.exists' => 'ID danh mục cha không tồn tại',
        ];
    }
}
