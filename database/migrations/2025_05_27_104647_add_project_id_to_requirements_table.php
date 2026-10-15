<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('requirements', function (Blueprint $table) {
            // Add the project_id column first
            $table->unsignedBigInteger('project_id')->after('id'); // Adjust the position as needed

            // Then define the foreign key constraint
            $table->foreign('project_id')
                  ->references('id')
                  ->on('projects')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('requirements', function (Blueprint $table) {
            // First, drop the foreign key
            $table->dropForeign(['project_id']);
            
            // Then, drop the project_id column
            $table->dropColumn('project_id');
        });
    }
};
