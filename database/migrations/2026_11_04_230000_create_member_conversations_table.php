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
        Schema::create('member_conversations', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id')->unsigned();
            $table->string('subject');
            $table->string('category', 50);
            $table->string('status', 30)->default('waiting_admin'); // waiting_admin, waiting_member, closed
            $table->timestamp('last_message_at')->nullable();
            $table->string('last_sender_type', 20)->nullable(); // customer, admin
            $table->integer('unread_for_admin_count')->unsigned()->default(1);
            $table->integer('unread_for_member_count')->unsigned()->default(0);
            $table->timestamps();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('cascade');

            $table->index(['customer_id', 'status'], 'mc_customer_status_idx');
            $table->index('status', 'mc_status_idx');
            $table->index('category', 'mc_category_idx');
            $table->index('last_message_at', 'mc_last_msg_idx');
        });

        Schema::create('member_conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->string('sender_type', 20); // customer, admin
            $table->integer('sender_id')->unsigned();
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('conversation_id')
                ->references('id')
                ->on('member_conversations')
                ->onDelete('cascade');

            $table->index(['conversation_id', 'sender_type', 'is_read'], 'mcm_conv_sender_read_idx');
            $table->index('created_at', 'mcm_created_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_conversation_messages');
        Schema::dropIfExists('member_conversations');
    }
};
