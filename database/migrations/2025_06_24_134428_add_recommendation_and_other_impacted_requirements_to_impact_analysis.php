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
            $table->text('recommendation')->nullable(); // To store recommendations
            $table->text('other_impacted_requirements')->nullable(); // To store other impacted requirements
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('impact_analyses', function (Blueprint $table) {
            $table->dropColumn(['recommendation', 'other_impacted_requirements']);
        });
    }
};
