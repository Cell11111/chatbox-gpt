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
        if (!Schema::hasTable('Conflicts1')) {
            Schema::create('Conflicts1', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('requirement_id_a');
                $table->unsignedBigInteger('requirement_id_b');
                $table->string('severity')->default('Low');
                $table->enum('status', ['Open', 'Resolved', 'Dismissed'])->default('Open');
                $table->text('summary');
                $table->text('suggested_merge')->nullable();
                $table->timestamps();

                $table->foreign('requirement_id_a')->references('id')->on('requirements')->onDelete('cascade');
                $table->foreign('requirement_id_b')->references('id')->on('requirements')->onDelete('cascade');
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('Conflicts1')) {
            Schema::drop('Conflicts1');
        }
    }
};
