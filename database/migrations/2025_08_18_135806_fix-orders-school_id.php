<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('UPDATE orders SET school_id = (SELECT school_id FROM users WHERE users.id = orders.users_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
