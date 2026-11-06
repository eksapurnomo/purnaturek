<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('buyer_verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('customer_id');
            $table->string('company_name')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('business_type')->nullable();
            $table->string('business_phone')->nullable();
            $table->text('business_address')->nullable();
            $table->string('website_url')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedInteger('reviewed_by')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('admins')->nullOnDelete();

            $table->index(['customer_id', 'status']);
            $table->index('status');
        });

        Schema::create('buyer_verification_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_verification_id')->constrained('buyer_verifications')->cascadeOnDelete();
            $table->string('document_type');
            $table->string('original_filename');
            $table->string('storage_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');
            $table->timestamps();

            $table->index('buyer_verification_id');
            $table->index('document_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyer_verification_documents');
        Schema::dropIfExists('buyer_verifications');
    }
};

