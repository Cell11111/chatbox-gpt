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
            Schema::create('impact_analyses', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('requirement_id');
        $table->text('impact');
        $table->timestamps();

        $table->foreign('requirement_id')->references('id')->on('requirements')->onDelete('cascade');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('impact_analyses');
    }
};
