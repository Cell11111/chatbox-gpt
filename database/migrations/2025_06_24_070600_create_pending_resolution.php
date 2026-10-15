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
        Schema::create('pending_resolution', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conflict_id');
            $table->unsignedBigInteger('requirement_a_id');
            $table->unsignedBigInteger('requirement_b_id')->nullable();
            $table->string('resolution');
            $table->json('updated_data');
            $table->unsignedBigInteger('proposed_by');
            $table->timestamps();

            $table->foreign('conflict_id')->references('id')->on('conflicts')->onDelete('cascade');
            $table->foreign('requirement_a_id')->references('id')->on('requirements')->onDelete('cascade');
            $table->foreign('requirement_b_id')->references('id')->on('requirements')->onDelete('cascade');
            $table->foreign('proposed_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_resolution');
    }
};
