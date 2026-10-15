<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('pending_requirements', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('project_id');
        $table->unsignedBigInteger('feature_id');
        $table->unsignedBigInteger('submitted_by');
        $table->string('title');
        $table->text('description');
        $table->string('category');
        $table->enum('priority', ['High', 'Medium', 'Low']);
        $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
        $table->timestamps();

        $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        $table->foreign('feature_id')->references('id')->on('project_features')->onDelete('cascade');
        $table->foreign('submitted_by')->references('id')->on('users')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('pending_requirements');
    }
};
