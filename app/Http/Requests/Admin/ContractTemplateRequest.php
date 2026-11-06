<?php

namespace App\Http\Requests\Admin;

use App\Models\ContractTemplate;
use App\Support\ContractAgreementManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContractTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('admin')->check();
    }

    public function rules(): array
    {
        $templateId = $this->route('id') ?: ($this->route('template')?->id ?? null);

        return [
            'title'             => ['required', 'string', 'max:255'],
            'identifier'        => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('contract_templates', 'identifier')->ignore($templateId),
            ],
            'category'          => ['required', 'string', 'max:100'],
            'description'       => ['nullable', 'string', 'max:2000'],
            'version'           => ['nullable', 'string', 'max:50'],
            'status'            => ['required', 'string', Rule::in([
                ContractTemplate::STATUS_DRAFT,
                ContractTemplate::STATUS_PUBLISHED,
                ContractTemplate::STATUS_ARCHIVED,
            ])],
            'member_accessible' => ['nullable', 'boolean'],
            'effective_date'    => ['nullable', 'date'],
            'file'              => [
                'nullable',
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

