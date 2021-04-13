<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTableCategoryTowsAddCategoryOnesId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('category_twos', function (Blueprint $table) {
            $table->integer("category_ones_id")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('category_twos', function (Blueprint $table) {
            $table->dropColumn("category_ones_id");
        });
    }
}
