<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $schoolManagerGroup = DB::table('groups')->where('type', 'school-admin')->first();
        DB::table('group_gates')->insert([
            'key' => 'admin-order',
            'groups_id' => $schoolManagerGroup->id,
            'users_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $schoolManagerGroup = DB::table('groups')->where('type', 'school-admin')->first();
        DB::table('group_gates')->where('groups_id', $schoolManagerGroup->id)->where('key', 'admin-order')->delete();
    }
};
