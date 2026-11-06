<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuyerVerificationDocument extends Model
{
    public const TYPE_BUSINESS_LICENSE = 'business_license';
    public const TYPE_TAX_CERTIFICATE  = 'tax_certificate';
    public const TYPE_RESALE_PERMIT    = 'resale_permit';
    public const TYPE_IDENTITY_PROOF   = 'identity_proof';
    public const TYPE_OTHER            = 'other';

    public const DOCUMENT_TYPES = [
        self::TYPE_BUSINESS_LICENSE => 'Business Registration / License',
        self::TYPE_TAX_CERTIFICATE  => 'Tax ID / VAT / GST Certificate',
        self::TYPE_RESALE_PERMIT    => 'Resale Certificate / Permit',
        self::TYPE_IDENTITY_PROOF   => 'Proof of Identity / Authorized Signatory',
        self::TYPE_OTHER            => 'Other Supporting Documentation',
    ];

    protected $fillable = [
        'buyer_verification_id',
        'document_type',
        'original_filename',
        'storage_path',
        'mime_type',
        'file_size',
    ];

    /**
     * Parent verification request.
     */
    public function buyerVerification(): BelongsTo
    {
        return $this->belongsTo(BuyerVerification::class);
    }

    /**
     * Document file name alias.
     */
    public function getFileNameAttribute(): string
    {
        return $this->original_filename;
    }

    /**
     * Document file path alias.
     */
    public function getFilePathAttribute(): string
    {
        return $this->storage_path;
    }

    /**
     * Human-readable document type label.
     */
    public function getDocumentTypeLabelAttribute(): string
    {
        return self::DOCUMENT_TYPES[$this->document_type] ?? ucfirst(str_replace('_', ' ', $this->document_type));
    }

    /**
     * Human-readable file size.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }
}
