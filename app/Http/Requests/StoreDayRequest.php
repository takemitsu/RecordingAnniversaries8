<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDayRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
            'anniv_at' => 'required|date_format:Y-m-d',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => '記念日の名前は必須です。',
            'name.max' => '記念日の名前は255文字以内で入力してください。',
            'anniv_at.required' => '記念日の日付は必須です。',
            'anniv_at.date_format' => '記念日の日付は正しい形式（YYYY-MM-DD）で入力してください。',
        ];
    }
}