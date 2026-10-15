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
        //     // Add the foreign key constraint if it doesn't already exist
        //     $table->foreign('project_id')
        //           ->references('id')
        //           ->on('projects')
        //           ->onDelete('cascade');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('requirements', function (Blueprint $table) {
        //     $table->dropForeign(['project_id']);
        // });
    }
};
