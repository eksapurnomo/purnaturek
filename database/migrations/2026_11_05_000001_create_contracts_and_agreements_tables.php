<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contract_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('identifier', 100)->unique('ct_identifier_unq');
            $table->string('category', 50)->default('custom');
            $table->text('description')->nullable();
            $table->string('version', 50)->default('1.0');
            $table->string('original_filename')->nullable();
            $table->string('storage_path')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('status', 20)->default('draft'); // draft, published, archived
            $table->boolean('member_accessible')->default(false);
            $table->date('effective_date')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')
                ->references('id')
                ->on('admins')
                ->onDelete('set null');

            $table->foreign('updated_by')
                ->references('id')
                ->on('admins')
                ->onDelete('set null');

            $table->index('category', 'ct_category_idx');
            $table->index('status', 'ct_status_idx');
            $table->index('member_accessible', 'ct_member_acc_idx');
        });

        Schema::create('member_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 50)->unique('mc_ref_num_unq');
            $table->string('title');
            $table->unsignedInteger('customer_id');
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('template_version', 50)->nullable();
            $table->string('category', 50)->nullable()->default('custom');
            $table->text('description')->nullable();
            $table->string('original_filename');
            $table->string('storage_path');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->string('file_hash', 64)->nullable();
            $table->string('status', 20)->default('draft'); // draft, sent, accepted, rejected, expired, cancelled, superseded
            $table->date('issued_date');
            $table->date('effective_date')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->text('member_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('cascade');

            $table->foreign('template_id')
                ->references('id')
                ->on('contract_templates')
                ->onDelete('set null');

            $table->foreign('created_by')
                ->references('id')
                ->on('admins')
                ->onDelete('set null');

            $table->foreign('updated_by')
                ->references('id')
                ->on('admins')
                ->onDelete('set null');

            $table->index(['customer_id', 'status'], 'mc_cust_status_idx');
            $table->index('status', 'mc_status_idx');
            $table->index('category', 'mc_category_idx');
            $table->index('expires_at', 'mc_expires_idx');
        });

        Schema::create('member_contract_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_contract_id');
            $table->string('event', 50); // created, updated, sent, viewed, accepted, rejected, expired, cancelled, superseded
            $table->string('actor_type', 20); // admin, customer, system
            $table->unsignedInteger('actor_id')->nullable();
            $table->string('actor_name')->nullable();
            $table->string('actor_email')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('file_hash', 64)->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('member_contract_id')
                ->references('id')
                ->on('member_contracts')
                ->onDelete('cascade');

            $table->index(['member_contract_id', 'event'], 'mce_contract_event_idx');
            $table->index('created_at', 'mce_created_idx');
        });

        // Seed initial 3 template records as draft (placeholders awaiting Admin legal upload)
        $now = now();
        DB::table('contract_templates')->insert([
            [
                'title'             => 'US Wholesale Buyer Agreement',
                'identifier'        => 'us_wholesale_buyer',
                'category'          => 'us_wholesale_buyer',
                'description'       => 'Master agreement governing wholesale purchasing relationship, commercial terms, order procedures, and mutual obligations between PurNatureK and US wholesale buyers.',
                'version'           => '1.0',
                'original_filename' => null,
                'storage_path'      => null,
                'mime_type'         => null,
                'file_size'         => null,
                'status'            => 'draft',
                'member_accessible' => false,
                'effective_date'    => null,
                'created_by'        => null,
                'updated_by'        => null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'title'             => 'Non-Disclosure Agreement (NDA)',
                'identifier'        => 'nda',
                'category'          => 'nda',
                'description'       => 'Mutual agreement governing the protection and non-disclosure of confidential business information, wholesale pricing, supplier networks, and proprietary product specifications.',
                'version'           => '1.0',
                'original_filename' => null,
                'storage_path'      => null,
                'mime_type'         => null,
                'file_size'         => null,
                'status'            => 'draft',
                'member_accessible' => false,
                'effective_date'    => null,
                'created_by'        => null,
                'updated_by'        => null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'title'             => 'US Distributor Agreement',
                'identifier'        => 'us_distributor',
                'category'          => 'us_distributor',
                'description'       => 'Commercial agreement governing authorized distribution rights, regional territories, brand standards, and supply commitments between PurNatureK and US wholesale distributors.',
                'version'           => '1.0',
                'original_filename' => null,
                'storage_path'      => null,
                'mime_type'         => null,
                'file_size'         => null,
                'status'            => 'draft',
                'member_accessible' => false,
                'effective_date'    => null,
                'created_by'        => null,
                'updated_by'        => null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_contract_events');
        Schema::dropIfExists('member_contracts');
        Schema::dropIfExists('contract_templates');
    }
};
