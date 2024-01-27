<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtraInfoToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('national_code')->nullable();
            $table->enum('gender', ['male', 'female'])->default('male');
            $table->string('home_tell')->nullable();
            $table->string('father_cell')->nullable();
            $table->string('mother_cell')->nullable();
            $table->integer('grade')->default(0);
            $table->string('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('national_code');
            $table->dropColumn('gender');
            $table->dropColumn('home_tell');
            $table->dropColumn('father_cell');
            $table->dropColumn('mother_cell');
            $table->dropColumn('grade');
            $table->dropColumn('description');
        });
    }
}
