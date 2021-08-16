<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChnageSomeColumnsToBeIndexProductDetailVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_detail_videos', function (Blueprint $table) {
            $table->index('products_id');
            $table->index('is_deleted');
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
            $table->dropIndex('product_detail_videos_products_id_index');
            $table->dropIndex('product_detail_videos_is_deleted_index');
        });
    }
}
