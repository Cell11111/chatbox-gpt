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
    // Schema::table('requirements', function (Blueprint $table) {
    //     $table->unsignedBigInteger('feature_id')->nullable()->after('project_id');

    //     // Add foreign key constraint
    //     $table->foreign('feature_id')->references('id')->on('project_features')->onDelete('set null');
    // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    // Schema::table('requirements', function (Blueprint $table) {
    //     $table->dropForeign(['feature_id']);
    //     $table->dropColumn('feature_id');
    // });
    }
};
