<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddNewItemToEnumInPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('pay', 'error', 'verify_error', 'amount_error', 'settle_error', 'success')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('pay', 'verify', 'settle', 'inquiry', 'reserval', 'error')");
    }
}
