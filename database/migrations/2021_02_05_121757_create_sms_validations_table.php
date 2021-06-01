<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsValidationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_validations', function (Blueprint $table) {
            $table->id();
            $table->integer('mobile')->unsigned()->unique();
            $table->integer('code')->unsigned();
            $table->text('user_info');
            $table->enum('type', ['register', 'forget_pass']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sms_validations');
    }
}
