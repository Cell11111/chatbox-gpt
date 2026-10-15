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
        Schema::table('pending_updates', function (Blueprint $table) {
            $table->enum('type', ['edit', 'new'])->default('edit')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pending_updates', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
