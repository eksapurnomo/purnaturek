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
        Schema::create('company_supply_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('Company & Supply Information');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft'); // draft, published, unpublished
            $table->dateTime('published_at')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('updated_by')
                ->references('id')
                ->on('admins')
                ->onDelete('set null');

            $table->index('status', 'csp_status_idx');
        });

        Schema::create('company_supply_sections', function (Blueprint $table) {
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
                ->on('company_supply_pages')
                ->onDelete('cascade');

            $table->unique(['page_id', 'section_key'], 'css_page_key_unq');
            $table->index(['page_id', 'is_enabled', 'sort_order'], 'css_order_idx');
        });

        // Seed default page in draft status
        $now = now();
        $pageId = DB::table('company_supply_pages')->insertGetId([
            'title'        => 'Company & Supply Information',
            'description'  => 'Official company operations, manufacturing arrangements, quality standards, inventory model, and supply cooperation guidelines for wholesale members.',
            'status'       => 'draft',
            'published_at' => null,
            'updated_by'   => null,
            'created_at'   => $now,
            'updated_at'   => $now,
        ]);

        // Seed the six initial content sections with titles, guidance notes, and empty content (no invented facts)
        DB::table('company_supply_sections')->insert([
            [
                'page_id'        => $pageId,
                'section_key'    => 'company_overview',
                'title'          => 'Company Overview',
                'guidance_notes' => 'Introduce the company, business operating model, company background, and dedicated wholesale member contact channels.',
                'content'        => null,
                'sort_order'     => 1,
                'is_enabled'     => true,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'page_id'        => $pageId,
                'section_key'    => 'manufacturing_sourcing',
                'title'          => 'Manufacturing & Sourcing',
                'guidance_notes' => 'Detail manufacturing partner arrangements, raw material sourcing standards, production facility protocols, and available processing capabilities.',
                'content'        => null,
                'sort_order'     => 2,
                'is_enabled'     => true,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'page_id'        => $pageId,
                'section_key'    => 'supply_capabilities',
                'title'          => 'Supply Capabilities',
                'guidance_notes' => 'Outline commercial supply capabilities, batch availability, replenishment schedules, supply planning, and capacity details.',
                'content'        => null,
                'sort_order'     => 3,
                'is_enabled'     => true,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'page_id'        => $pageId,
                'section_key'    => 'quality_product_handling',
                'title'          => 'Quality & Product Handling',
                'guidance_notes' => 'Describe quality assurance protocols, third-party testing, purity verification, climate-controlled storage practices, and references to available certificates.',
                'content'        => null,
                'sort_order'     => 4,
                'is_enabled'     => true,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'page_id'        => $pageId,
                'section_key'    => 'inventory_fulfillment',
                'title'          => 'Inventory & Fulfillment',
                'guidance_notes' => 'Explain the US inventory operating model, domestic distribution points, packing standards, carrier coordination, and verified lead times.',
                'content'        => null,
                'sort_order'     => 5,
                'is_enabled'     => true,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'page_id'        => $pageId,
                'section_key'    => 'supply_cooperation',
                'title'          => 'Supply Cooperation',
                'guidance_notes' => 'Define wholesale coordination expectations, distributor communication workflows, escalation paths, and commercial partnership principles.',
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
        Schema::dropIfExists('company_supply_sections');
        Schema::dropIfExists('company_supply_pages');
    }
};

