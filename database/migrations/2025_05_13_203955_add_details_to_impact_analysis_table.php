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
        Schema::table('impact_analyses', function (Blueprint $table) {
            $table->string('field');
            $table->text('old');
            $table->text('new');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('impact_analysis', function (Blueprint $table) {
            //
        });
    }
};
