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
        Schema::table('defects', function (Blueprint $table) {
            $table->dropColumn('requirement_id');
        });

        Schema::table('defects', function (Blueprint $table) {
            // Add it back with foreign key constraint
            $table->foreignId('requirement_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('defects', function (Blueprint $table) {
            $table->dropForeign(['requirement_id']);
            $table->dropColumn('requirement_id');

            // Optionally, add back the plain column
            $table->unsignedBigInteger('requirement_id')->nullable();
        });
    }
};
