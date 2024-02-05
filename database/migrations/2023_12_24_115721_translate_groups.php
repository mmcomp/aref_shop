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
        DB::table('groups')->where('type', 'admin')->update(["name" => "مدیر ارشد"]);
        DB::table('groups')->where('type', 'user')->update(["name" => "دانش‌آموز"]);
        DB::table('groups')->where('type', 'teacher')->update(["name" => "دبیر"]);
        DB::table('groups')->where('type', 'admin_reading_station')->update(["name" => "مدیر کلیه شعب سالن مطالعه"]);
        DB::table('groups')->where('type', 'admin_reading_station_branch')->update(["name" => "مدیر شعبه سالن مطالعه"]);
        DB::table('groups')->where('type', 'user_reading_station_branch')->update(["name" => "اپراتور سالن مطالعه"]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('groups')->where('type', 'admin')->update(["name" => "Admin"]);
        DB::table('groups')->where('type', 'user')->update(["name" => "Registered"]);
        DB::table('groups')->where('type', 'teacher')->update(["name" => "Teachers"]);
        DB::table('groups')->where('type', 'admin_reading_station')->update(["name" => "Reading Station Admin"]);
        DB::table('groups')->where('type', 'admin_reading_station_branch')->update(["name" => "Reading Station Branch Admin"]);
        DB::table('groups')->where('type', 'user_reading_station_branch')->update(["name" => "Reading Station Branch User"]);
    }
};
