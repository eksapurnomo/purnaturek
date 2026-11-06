<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MemberConversationReplyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->guard('customer')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:2', 'max:5000'],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'message.required' => 'Please write a message before sending.',
            'message.min'      => 'Your reply must be at least 2 characters.',
            'message.max'      => 'Your reply cannot exceed 5,000 characters.',
        ];
    }
}

