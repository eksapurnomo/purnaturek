<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductDocument;
use App\Support\ProductDocumentationManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductDocumentRequest extends FormRequest
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
        $isCreate = $this->isMethod('POST') && ! $this->route('id');

        return [
            'title'         => ['required', 'string', 'max:255'],
            'product_id'    => ['required', 'integer', 'exists:products,id'],
            'document_type' => ['required', 'string', Rule::in(array_keys(ProductDocument::DOCUMENT_TYPES))],
            'description'   => ['nullable', 'string', 'max:2000'],
            'version'       => ['nullable', 'string', 'max:50'],
            'issue_date'    => ['nullable', 'date'],
            'expiry_date'   => ['nullable', 'date', 'after_or_equal:issue_date'],
            'status'        => ['required', 'string', Rule::in([ProductDocument::STATUS_DRAFT, ProductDocument::STATUS_PUBLISHED])],
            'sort_order'    => ['nullable', 'integer', 'min:0'],
            'file'          => [
                $isCreate ? 'required' : 'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png,webp',
                'max:' . ProductDocumentationManager::MAX_FILE_SIZE_KB,
            ],
        ];
    }

    /**
     * Additional validation for actual file MIME inspection.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if ($this->hasFile('file') && $this->file('file')->isValid()) {
                $file = $this->file('file');
                $mime = strtolower((string) $file->getMimeType());

                if (! in_array($mime, ProductDocumentationManager::ALLOWED_MIME_TYPES, true)) {
                    $v->errors()->add('file', "The uploaded file MIME type '{$mime}' is not permitted. Permitted formats: PDF, JPG, JPEG, PNG, WEBP.");
                }
            }
        });
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'product_id.exists'           => 'The selected product does not exist.',
            'document_type.in'            => 'The selected document type is invalid.',
            'expiry_date.after_or_equal'  => 'Expiry date must be on or after the issue date.',
            'file.max'                    => 'The document must not exceed 10 MB in file size.',
            'file.mimes'                  => 'The document must be a file of type: PDF, JPG, JPEG, PNG, WEBP.',
        ];
    }
}

