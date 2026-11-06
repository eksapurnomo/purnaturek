<?php

namespace App\Http\Requests;

use App\Models\MemberConversation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberConversationCreateRequest extends FormRequest
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
            'subject'  => ['required', 'string', 'min:3', 'max:255'],
            'category' => ['required', 'string', Rule::in(array_keys(MemberConversation::CATEGORIES))],
            'message'  => ['required', 'string', 'min:5', 'max:5000'],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'subject.required'  => 'Please provide a subject for your inquiry.',
            'subject.min'       => 'The subject must be at least 3 characters.',
            'category.required' => 'Please select an inquiry category.',
            'category.in'       => 'The selected inquiry category is invalid.',
            'message.required'  => 'Please write your message.',
            'message.min'       => 'Your message must be at least 5 characters.',
            'message.max'       => 'Your message cannot exceed 5,000 characters.',
        ];
    }
}

