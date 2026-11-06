<?php

namespace App\Http\Requests\Admin;

use App\Models\WholesalePartnershipPage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WholesalePartnershipPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('admin')->check();
    }

    public function rules(): array
    {
        return [
            'title'                 => ['required', 'string', 'max:255'],
            'description'           => ['nullable', 'string', 'max:1000'],
            'status'                => ['required', 'string', Rule::in([
                WholesalePartnershipPage::STATUS_DRAFT,
                WholesalePartnershipPage::STATUS_PUBLISHED,
                WholesalePartnershipPage::STATUS_UNPUBLISHED,
            ])],
            'sections'              => ['required', 'array'],
            'sections.*.id'         => ['required', 'integer', 'exists:wholesale_partnership_sections,id'],
            'sections.*.title'      => ['required', 'string', 'max:255'],
            'sections.*.content'    => ['nullable', 'string', 'max:50000'],
            'sections.*.sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'sections.*.is_enabled' => ['nullable', 'boolean'],
        ];
    }
}

