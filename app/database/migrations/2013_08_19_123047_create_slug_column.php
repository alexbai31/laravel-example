<?php

use Illuminate\Database\Migrations\Migration;

class CreateSlugColumn extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("build_types", function ($table) {
            $table->string("slug");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("build_types", function ($table) {
            $table->dropColumn("slug");
        });
    }

}