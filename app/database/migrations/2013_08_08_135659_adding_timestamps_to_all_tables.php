<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddingTimestampsToAllTables extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('versions', function (Blueprint $table) {
            $table->timestamps();
        });
        Schema::table('build_types', function (Blueprint $table) {
            $table->timestamps();
        });
        Schema::table('builds', function (Blueprint $table) {
            $table->timestamps();
        });
        Schema::table('labels', function (Blueprint $table) {
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('versions', function (Blueprint $table) {
            $table->dropColumn('created_at', 'updated_at');
        });
        Schema::table('build_types', function (Blueprint $table) {
            $table->dropColumn('created_at', 'updated_at');
        });
        Schema::table('builds', function (Blueprint $table) {
            $table->dropColumn('created_at', 'updated_at');
        });
        Schema::table('labels', function (Blueprint $table) {
            $table->dropColumn('created_at', 'updated_at');
        });
    }

}