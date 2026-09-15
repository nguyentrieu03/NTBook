<?php

namespace App\Http\Requests\Attribute;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateAttributeRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9_-]+$/i', 'unique:attributes,code'],
            'values' => ['nullable', 'array'],
            'values.*' => ['nullable', 'string', 'max:120']
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên thuộc tính là bắt buộc',
            'name.string' => 'Tên thuộc tính phải là một chuỗi',
            'name.max' => 'Tên thuộc tính không được vượt quá 120 ký tự',
            'code.required' => 'Mã thuộc tính là bắt buộc',
            'code.string' => 'Mã thuộc tính phải là một chuỗi',
            'code.max' => 'Mã thuộc tính không được vượt quá 60 ký tự',
            'code.regex' => 'Mã thuộc tính chỉ được chứa các chữ cái thường, số và dấu gạch dưới',
            'code.unique' => 'Mã thuộc tính đã tồn tại',
            'values.array' => 'Giá trị thuộc tính phải là một mảng',
            'values.*.string' => 'Giá trị thuộc tính phải là một chuỗi',    
            'values.*.max' => 'Giá trị thuộc tính không được vượt quá 120 ký tự',
        ];
    }
}
