<?php

namespace App\Http\Requests;

use App\Models\BuyerVerification;
use App\Models\BuyerVerificationDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BuyerVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->guard('customer')->check();
    }

    /**
     * Prepare inputs for validation.
     * Clean up empty file upload slots from multi-input forms.
     */
    protected function prepareForValidation(): void
    {
        $files = $this->file('documents', []);
        $types = $this->input('document_types', []);

        if (is_array($files)) {
            $filteredFiles = [];
            $filteredTypes = [];

            foreach ($files as $idx => $file) {
                if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {
                    $filteredFiles[] = $file;
                    $filteredTypes[] = $types[$idx] ?? BuyerVerificationDocument::TYPE_OTHER;
                }
            }

            if (! empty($filteredFiles)) {
                $this->files->set('documents', $filteredFiles);
                $this->merge([
                    'document_types' => $filteredTypes,
                ]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'company_name'     => ['required', 'string', 'max:255'],
            'tax_id'           => ['required', 'string', 'max:100'],
            'business_type'    => ['required', 'string', Rule::in(array_keys(BuyerVerification::BUSINESS_TYPES))],
            'business_phone'   => ['required', 'string', 'max:50'],
            'business_address' => ['required', 'string', 'max:500'],
            'website_url'      => ['nullable', 'url', 'max:255'],
            'documents'        => ['required', 'array', 'min:1'],
            'documents.*'      => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,webp',
                'max:10240', // 10 MB limit
            ],
            'document_types'   => ['required', 'array'],
            'document_types.*' => [
                'required',
                'string',
                Rule::in(array_keys(BuyerVerificationDocument::DOCUMENT_TYPES)),
            ],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'company_name.required'     => 'Please provide your registered legal company name.',
            'tax_id.required'           => 'Please provide your Business Tax ID, VAT, EIN, or Registration Number.',
            'business_type.required'    => 'Please select your primary business type.',
            'business_phone.required'   => 'Please provide a commercial contact telephone number.',
            'business_address.required' => 'Please provide your registered commercial business address.',
            'documents.required'        => 'Please upload at least one valid business credential document (Business License or Tax ID).',
            'documents.*.mimes'         => 'Documents must be PDF, JPG, JPEG, PNG, or WEBP format.',
            'documents.*.max'           => 'Document file size must not exceed 10 MB per file.',
        ];
    }
}

