<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTableCategoryThreesAddCategoryTwosId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('category_threes', function (Blueprint $table) {
            $table->integer("category_twos_id")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('category_threes', function (Blueprint $table) {
            $table->dropColumn("category_twos_id");
        });
    }
}
