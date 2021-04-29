<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFourNullableFieldsInProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('start_date', 255)->nullable()->after('type');
            $table->string('days', 255)->nullable()->after('type');
            $table->string('hour', 255)->nullable()->after('type');
            $table->string('education_system', 255)->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('start_date');
            $table->dropColumn('days');
            $table->dropColumn('hour');
            $table->dropColumn('education_system');
        });
    }
}
