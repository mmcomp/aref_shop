<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sky_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title')->nullable();
            $table->integer('status')->default(1);
            $table->integer('service_id')->nullable();
            $table->boolean('guest_login')->default(false);
            $table->boolean('op_login_first')->default(false);
            $table->integer('max_users')->nullable();
            $table->bigInteger(column: 'room)id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sky_rooms');
    }
};
