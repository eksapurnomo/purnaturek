<?php

namespace App\Support;

use App\Mail\BuyerVerificationApproved;
use App\Mail\BuyerVerificationRejected;
use App\Mail\BuyerVerificationSubmitted;
use App\Models\BuyerVerification;
use App\Models\BuyerVerificationDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Webkul\Customer\Models\Customer;

class BuyerVerificationManager
{
    /**
     * Determine if a customer has been approved as a Verified Wholesale Buyer.
     */
    public static function isVerifiedBuyer(?Customer $customer): bool
    {
        if (! $customer) {
            return false;
        }

        return BuyerVerification::where('customer_id', $customer->id)
            ->where('status', BuyerVerification::STATUS_APPROVED)
            ->exists();
    }

    /**
     * Get the current verification status for a customer.
     * Returns: 'unverified', 'pending', 'approved', or 'rejected'.
     */
    public static function getVerificationStatus(?Customer $customer): string
    {
        if (! $customer) {
            return 'unverified';
        }

        $latest = self::getLatestVerification($customer);

        return $latest ? $latest->status : 'unverified';
    }

    /**
     * Get the latest verification record for a customer.
     */
    public static function getLatestVerification(?Customer $customer): ?BuyerVerification
    {
        if (! $customer) {
            return null;
        }

        return BuyerVerification::where('customer_id', $customer->id)
            ->latest('id')
            ->first();
    }

    /**
     * Check if customer is eligible to submit/resubmit an application.
     */
    public static function canSubmitApplication(?Customer $customer): bool
    {
        if (! $customer) {
            return false;
        }

        $latest = self::getLatestVerification($customer);

        if (! $latest) {
            return true;
        }

        return $latest->status === BuyerVerification::STATUS_REJECTED;
    }

    /**
     * Submit or resubmit a buyer verification application.
     *
     * @param  Customer  $customer
     * @param  array  $data
     * @param  array<string, UploadedFile|UploadedFile[]>  $files
     * @return BuyerVerification
     *
     * @throws ValidationException
     */
    public static function submitApplication(Customer $customer, array $data, array $files): BuyerVerification
    {
        // Prevent duplicate simultaneously active/pending submissions
        $activePending = BuyerVerification::where('customer_id', $customer->id)
            ->where('status', BuyerVerification::STATUS_PENDING)
            ->first();

        if ($activePending) {
            throw ValidationException::withMessages([
                'verification' => 'You already have a verification application pending review.',
            ]);
        }

        return DB::transaction(function () use ($customer, $data, $files) {
            $verification = BuyerVerification::create([
                'customer_id'      => $customer->id,
                'company_name'     => trim($data['company_name'] ?? ''),
                'tax_id'           => trim($data['tax_id'] ?? ''),
                'business_type'    => trim($data['business_type'] ?? ''),
                'business_phone'   => trim($data['business_phone'] ?? $data['phone'] ?? ''),
                'business_address' => trim($data['business_address'] ?? $data['address'] ?? ''),
                'website_url'      => trim($data['website_url'] ?? ''),
                'status'           => BuyerVerification::STATUS_PENDING,
                'rejection_reason' => null,
                'submitted_at'     => now(),
            ]);

            $disk = Storage::disk('private');
            $directory = 'buyer_verifications/' . $customer->id;

            foreach ($files as $documentType => $fileGroup) {
                $fileList = is_array($fileGroup) ? $fileGroup : [$fileGroup];

                foreach ($fileList as $file) {
                    if (! $file instanceof UploadedFile) {
                        continue;
                    }

                    $extension = strtolower($file->getClientOriginalExtension());
                    $safeFilename = Str::random(40) . '.' . $extension;
                    $storagePath = $file->storeAs($directory, $safeFilename, 'private');

                    BuyerVerificationDocument::create([
                        'buyer_verification_id' => $verification->id,
                        'document_type'         => $documentType,
                        'original_filename'     => $file->getClientOriginalName(),
                        'storage_path'          => $storagePath,
                        'mime_type'             => $file->getClientMimeType(),
                        'file_size'             => $file->getSize(),
                    ]);
                }
            }

            // Attempt to notify admin (silently fail if mail unconfigured in dev)
            try {
                $adminEmail = config('mail.from.address', 'admin@purnaturek.com');
                Mail::to($adminEmail)->send(new BuyerVerificationSubmitted($verification));
            } catch (\Throwable $e) {
                Log::warning('Buyer verification submission email failed to send: ' . $e->getMessage());
            }

            return $verification;
        });
    }

    /**
     * Approve a verification application.
     */
    public static function approveApplication(BuyerVerification $verification, $admin): BuyerVerification
    {
        return DB::transaction(function () use ($verification, $admin) {
            $verification->update([
                'status'           => BuyerVerification::STATUS_APPROVED,
                'reviewed_by'      => $admin?->id,
                'reviewed_at'      => now(),
                'rejection_reason' => null,
            ]);

            // Attempt to notify member
            try {
                if ($customer = $verification->customer) {
                    Mail::to($customer->email)->send(new BuyerVerificationApproved($verification));
                }
            } catch (\Throwable $e) {
                Log::warning('Buyer verification approval email failed to send: ' . $e->getMessage());
            }

            return $verification->fresh();
        });
    }

    /**
     * Reject a verification application with a recorded reason.
     */
    public static function rejectApplication(BuyerVerification $verification, $admin, string $reason): BuyerVerification
    {
        return DB::transaction(function () use ($verification, $admin, $reason) {
            $verification->update([
                'status'           => BuyerVerification::STATUS_REJECTED,
                'reviewed_by'      => $admin?->id,
                'reviewed_at'      => now(),
                'rejection_reason' => trim($reason),
            ]);

            // Attempt to notify member
            try {
                if ($customer = $verification->customer) {
                    Mail::to($customer->email)->send(new BuyerVerificationRejected($verification, $reason));
                }
            } catch (\Throwable $e) {
                Log::warning('Buyer verification rejection email failed to send: ' . $e->getMessage());
            }

            return $verification->fresh();
        });
    }

    /**
     * Securely stream a private business document to an authorized user.
     */
    public static function streamDocument(BuyerVerificationDocument $document, $user, bool $isAdmin = false): StreamedResponse
    {
        if (! $isAdmin) {
            $documentOwnerId = $document->buyerVerification?->customer_id;

            if (! $user || (int) $user->id !== (int) $documentOwnerId) {
                abort(403, 'Unauthorized access to business document.');
            }
        }

        $disk = Storage::disk('private');

        if (! $disk->exists($document->storage_path)) {
            abort(404, 'The requested document file could not be found.');
        }

        return $disk->response($document->storage_path, $document->original_filename);
    }
}
