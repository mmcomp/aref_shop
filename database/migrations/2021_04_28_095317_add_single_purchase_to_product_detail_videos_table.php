<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSinglePurchaseToProductDetailVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_detail_videos', function (Blueprint $table) {
            $table->boolean('single_purchase')->default(0)->after('extraordinary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_detail_videos', function (Blueprint $table) {
            $table->dropColumn('single_purchase');
        });
    }
}
