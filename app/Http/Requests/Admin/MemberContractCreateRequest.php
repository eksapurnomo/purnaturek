<?php

namespace App\Http\Requests\Admin;

use App\Models\MemberContract;
use App\Support\ContractAgreementManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberContractCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('admin')->check();
    }

    public function rules(): array
    {
        return [
            'title'          => ['required', 'string', 'max:255'],
            'customer_id'    => ['required', 'integer', 'exists:customers,id'],
            'template_id'    => ['nullable', 'integer', 'exists:contract_templates,id'],
            'issued_date'    => ['nullable', 'date'],
            'effective_date' => ['nullable', 'date'],
            'expires_at'     => ['nullable', 'date'],
            'status'         => ['nullable', 'string', Rule::in([MemberContract::STATUS_DRAFT, MemberContract::STATUS_SENT])],
            'admin_notes'    => ['nullable', 'string', 'max:2000'],
            'file'           => [
                'required',
                'file',
                'mimes:pdf,docx,doc',
                'max:' . ContractAgreementManager::MAX_FILE_SIZE_KB,
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->hasFile('file')) {
                $file = $this->file('file');
                if ($file && $file->isValid()) {
                    $mime = strtolower((string) $file->getMimeType());
                    if (!in_array($mime, ContractAgreementManager::ALLOWED_MIME_TYPES, true)) {
                        $validator->errors()->add('file', "The uploaded file format ({$mime}) is not permitted. Permitted formats: PDF, DOCX, DOC.");
                    }
                }
            }
        });
    }
}

