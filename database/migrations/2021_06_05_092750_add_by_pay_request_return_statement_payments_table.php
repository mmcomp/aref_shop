<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddByPayRequestReturnStatementPaymentsTable extends Migration
{
    public function Have($field)
    {
        if (Schema::hasColumn('payments', $field)) {
            Schema::table('payments', function (Blueprint $table) use($field) {
                $table->dropColumn($field);
            });
        }
    }
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
            $table->text('‫‪sale_order_id‬‬')->after('price');
            $table->text('‫‪sale_reference_id‬‬')->after('price');
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
            //$table->dropColumn("sale_order_id"‬‬);
            $table->dropColumn('sale_reference_id');
        });
        //$table->dropColumn('ref_id');
        // $this->Have('ref_id');
        // $this->Have('res_code');
        // $this->Have('sale_order_id');
        // $this->Have('sale_reference_id');
        // if (Schema::hasColumn('payments', 'ref_id')){
        //     Schema::table('payments', function (Blueprint $table) {
        //         $table->dropColumn('ref_id');
        //     });
        // }
        // if (Schema::hasColumn('payments', 'res_code')){
        //     Schema::table('payments', function (Blueprint $table) {
        //         $table->dropColumn('res_code');
        //     });
        // }
        // if (Schema::hasColumn('payments', 'sale_order_id')){
        //     Schema::table('payments', function (Blueprint $table) {
        //         $table->dropColumn('ref_id');
        //     });
        // }
        // if (Schema::hasColumn('payments', 'sale_reference_id')){
        //     Schema::table('payments', function (Blueprint $table) {
        //         $table->dropColumn('sale_reference_id');
        //     });
        // }
       
    }
}
