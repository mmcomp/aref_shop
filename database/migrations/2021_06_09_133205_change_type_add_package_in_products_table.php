<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeTypeAddPackageInProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE products MODIFY COLUMN type ENUM('normal', 'download', 'chairs', 'video', 'package')");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE products MODIFY COLUMN type ENUM('normal', 'download', 'chairs', 'video')");
    }
}
