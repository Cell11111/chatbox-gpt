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
        if (!Schema::hasTable('requirement_conflicts')) {
            Schema::create('requirement_conflicts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('requirement_id_a');
                $table->unsignedBigInteger('requirement_id_b');
                $table->text('summary');
                $table->enum('severity', ['High', 'Medium', 'Low']);
                $table->string('status')->default('Open');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('requirement_conflicts')) {
            Schema::drop('requirement_conflicts');
        }
    }
};
