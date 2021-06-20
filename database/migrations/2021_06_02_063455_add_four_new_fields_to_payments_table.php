<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFourNewFieldsToPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('ref_id', 255)->after('price');
            $table->string('res_code', 255)->after('price');
            $table->text('sale_order_id')->after('price');
            $table->text('sale_reference_id')->after('price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('ref_id');
            $table->dropColumn('res_code');
            $table->dropColumn('sale_order_id');
            $table->dropColumn('sale_reference_id');
        });
    }
}
