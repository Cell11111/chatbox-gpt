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
    Schema::table('requirement_versions', function (Blueprint $table) {
        $table->unsignedBigInteger('project_id')->after('requirement_id');

        $table->foreign('project_id')
            ->references('id')
            ->on('projects')
            ->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('requirement_versions', function (Blueprint $table) {
        $table->dropForeign(['project_id']);
        $table->dropColumn('project_id');
    });
}
};
