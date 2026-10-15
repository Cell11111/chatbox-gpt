<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('requirement_versions', function (Blueprint $table) {
            // Add the 'feature_id' column first
            $table->unsignedBigInteger('feature_id')->nullable(); // Adjust nullable based on your requirements

            // Add foreign key constraint
            $table->foreign('feature_id')->references('id')->on('project_features')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requirement_versions', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['feature_id']);

            // Drop the column
            $table->dropColumn('feature_id');
        });
    }
};
