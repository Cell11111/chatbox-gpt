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
        Schema::create('conflicts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requirement_a_id');
            $table->unsignedBigInteger('requirement_b_id');
            $table->string('summary');
            $table->string('severity')->default('Medium');
            $table->string('status')->default('Open');
            $table->timestamps();

            $table->foreign('requirement_a_id')->references('id')->on('requirements')->onDelete('cascade');
            $table->foreign('requirement_b_id')->references('id')->on('requirements')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conflicts');
    }
};
