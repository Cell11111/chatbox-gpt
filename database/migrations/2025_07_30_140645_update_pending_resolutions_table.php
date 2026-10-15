<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePendingResolutionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pending_resolution', function (Blueprint $table) {
            // Add new columns
            $table->string('status')->default('Pending');
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            
            // Add foreign key for reviewed_by
            $table->foreign('reviewed_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pending_resolution', function (Blueprint $table) {
            // Remove foreign key first
            $table->dropForeign(['reviewed_by']);
            
            // Remove columns
            $table->dropColumn([
                'status',
                'reviewed_at',
                'reviewed_by',
            ]);
        });
    }
};
