<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderingToCategoryOnesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('category_ones', function (Blueprint $table) {
            $table->integer('ordering')->after('published')->unsigned()->default(1)->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('category_ones', function (Blueprint $table) {
            $table->dropColumn('ordering');
        });
    }
}
