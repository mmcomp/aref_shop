<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConferenceOtherFeildToProductDetailVideos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_detail_videos', function (Blueprint $table) {
            $table->text('free_conference_before_start_text')->nullable();
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
            $table->dropColumn('free_conference_before_start_text');
        });
    }
}
