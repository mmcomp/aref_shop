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
        $schoolAdminGroupId = DB::table('groups')->where('type', 'school-admin')->first()->id;
        DB::table('group_gates')->insert([
            ['groups_id' => $schoolAdminGroupId, 'key' => 'coupon'],
            ['groups_id' => $schoolAdminGroupId, 'key' => 'user'],
            ['groups_id' => $schoolAdminGroupId, 'key' => 'product'],
            ['groups_id' => $schoolAdminGroupId, 'key' => 'report-sale'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $schoolAdminGroupId = DB::table('groups')->where('type', 'school-admin')->first()->id;
        DB::table('group_gates')->where('groups_id', $schoolAdminGroupId)->delete();
    }
};
