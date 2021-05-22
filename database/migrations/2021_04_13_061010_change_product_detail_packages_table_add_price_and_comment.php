<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeProductDetailPackagesTableAddPriceAndComment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_detail_packages', function (Blueprint $table) {
            $table->integer("price")->default(0);
            $table->integer("child_products_id")->comment("محصول زیر شاخه بسته")->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_detail_packages', function (Blueprint $table) {
            $table->dropColumn("price");
        });
    }
}
