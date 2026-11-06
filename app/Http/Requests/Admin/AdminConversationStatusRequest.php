<?php

namespace App\Http\Requests\Admin;

use App\Models\MemberConversation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminConversationStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->guard('admin')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(array_keys(MemberConversation::STATUSES))],
        ];
    }
}

