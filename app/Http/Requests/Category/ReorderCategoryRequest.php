<?php

namespace App\Http\Requests\Category;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReorderCategoryRequest extends FormRequest
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
            'tree' => ['required', 'array', 'min:1'],
            'tree.*.id' => ['required', 'integer', 'min:1'],
            'tree.*.children' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'tree.array' => 'Dữ liệu cây danh mục không hợp lệ',
            'tree.min' => 'Cây danh mục không được rỗng',
            'tree.*.id.required' => 'Thiếu ID danh mục',
            'tree.*.id.integer' => 'ID danh mục phải là số nguyên',
            'tree.*.id.min' => 'ID danh mục không hợp lệ',
            'tree.*.children.array' => 'Danh mục con phải là mảng',
        ];
    }
}
