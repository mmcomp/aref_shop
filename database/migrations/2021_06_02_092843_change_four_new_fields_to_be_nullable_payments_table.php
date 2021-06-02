<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFourNewFieldsToBeNullablePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('ref_id')->nullable()->change();
            $table->string('res_code')->nullable()->change();
            $table->text('sale_order_id')->nullable()->change();
            $table->text('sale_reference_id')->nullable()->change();
            $table->text('bank_returned')->nullable()->change();
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
            $table->string('ref_id')->nullable(false)->change();
            $table->string('res_code')->nullable(false)->change();
            $table->text('sale_order_id')->nullable(false)->change();
            $table->text('sale_reference_id')->nullable(false)->change();
            $table->text('bank_returned')->nullable(false)->change();
        });
    }
}
