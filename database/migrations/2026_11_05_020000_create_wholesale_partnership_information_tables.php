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
        Schema::create('wholesale_partnership_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('Wholesale Partnership Information');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft'); // draft, published, unpublished
            $table->dateTime('published_at')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('updated_by')
                ->references('id')
                ->on('admins')
                ->onDelete('set null');

            $table->index('status', 'wpp_status_idx');
        });

        Schema::create('wholesale_partnership_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_id');
            $table->string('section_key', 50);
            $table->string('title');
            $table->text('guidance_notes')->nullable();
            $table->longText('content')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->foreign('page_id')
                ->references('id')
                ->on('wholesale_partnership_pages')
                ->onDelete('cascade');

            $table->unique(['page_id', 'section_key'], 'wps_page_key_unq');
            $table->index(['page_id', 'is_enabled', 'sort_order'], 'wps_order_idx');
        });

        // Seed default page in draft status
        $now = now();
        $pageId = DB::table('wholesale_partnership_pages')->insertGetId([
            'title'        => 'Wholesale Partnership Information',
            'description'  => 'Explore commercial wholesale cooperation models, distributor onboarding requirements, manufacturing partner arrangements, and partnership activation pathways.',
            'status'       => 'draft',
            'published_at' => null,
            'updated_by'   => null,
            'created_at'   => $now,
            'updated_at'   => $now,
        ]);

        // Seed the six initial partnership sections with titles, guidance notes, and empty content (no invented facts)
        DB::table('wholesale_partnership_sections')->insert([
            [
                'page_id'        => $pageId,
                'section_key'    => 'us_wholesale_buyer_partnership',
                'title'          => 'US Wholesale Buyer Partnership',
                'guidance_notes' => 'Overview of commercial wholesale purchasing, buyer profiles, registration and verification prerequisites, product discussion, and transaction workflow.',
                'content'        => null,
                'sort_order'     => 1,
                'is_enabled'     => true,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'page_id'        => $pageId,
                'section_key'    => 'us_distributor_partnership',
                'title'          => 'US Distributor Partnership',
                'guidance_notes' => 'Distributor cooperation overview, qualifications, distribution and inventory coordination, logistics fulfillment, and territory arrangements subject to written agreement.',
                'content'        => null,
                'sort_order'     => 2,
                'is_enabled'     => true,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'page_id'        => $pageId,
                'section_key'    => 'manufacturing_partner',
                'title'          => 'Manufacturing Partner',
                'guidance_notes' => 'Manufacturing cooperation arrangements, production scheduling, raw material sourcing standards, quality parameters, and partner communication workflow.',
                'content'        => null,
                'sort_order'     => 3,
                'is_enabled'     => true,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'page_id'        => $pageId,
                'section_key'    => 'partnership_requirements',
                'title'          => 'Partnership Requirements',
                'guidance_notes' => 'Required corporate identity verification, tax documentation, business credentials, confidentiality terms, and distinctions between general registration, buyer verification, and partner evaluation.',
                'content'        => null,
                'sort_order'     => 4,
                'is_enabled'     => true,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'page_id'        => $pageId,
                'section_key'    => 'partnership_onboarding_process',
                'title'          => 'Partnership Onboarding Process',
                'guidance_notes' => 'Step-by-step onboarding journey: registration, operational review, model selection, administration contact, credential review, agreement preparation, and partnership activation.',
                'content'        => null,
                'sort_order'     => 5,
                'is_enabled'     => true,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'page_id'        => $pageId,
                'section_key'    => 'commercial_cooperation_models',
                'title'          => 'Commercial Cooperation Models',
                'guidance_notes' => 'High-level structures: wholesale purchasing, distributor distribution, manufacturing collaboration, and long-term supply planning. All terms subject to individual contract.',
                'content'        => null,
                'sort_order'     => 6,
                'is_enabled'     => true,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wholesale_partnership_sections');
        Schema::dropIfExists('wholesale_partnership_pages');
    }
};

