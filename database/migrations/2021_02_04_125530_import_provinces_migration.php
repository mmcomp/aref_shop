<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ImportProvincesMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = "
        INSERT INTO `provinces` (`id`, `name`, `created_at`, `updated_at`) VALUES
        (1, 'خراسان رضوی', NULL, NULL),
        (2, 'تهران', NULL, NULL),
        (3, 'اردبیل', NULL, NULL),
        (4, 'اصفهان', NULL, NULL),
        (5, 'البرز', NULL, NULL),
        (6, 'ایلام', NULL, NULL),
        (7, 'بوشهر', NULL, NULL),
        (8, 'آذربایجان غربی', NULL, NULL),
        (9, 'چهارمحال وبختیاری', NULL, NULL),
        (10, 'خراسان جنوبی', NULL, NULL),
        (11, 'آذربایجان شرقی', NULL, NULL),
        (12, 'خراسان شمالی', NULL, NULL),
        (13, 'خوزستان', NULL, NULL),
        (14, 'زنجان', NULL, NULL),
        (15, 'سمنان', NULL, NULL),
        (16, 'سیستان وبلوچستان', NULL, NULL),
        (17, 'فارس', NULL, NULL),
        (18, 'قزوین', NULL, NULL),
        (19, 'قم', NULL, NULL),
        (20, 'کردستان', NULL, NULL),
        (21, 'کرمان', NULL, NULL),
        (22, 'کرمانشاه', NULL, NULL),
        (23, 'کهگیلویه وبویراحمد', NULL, NULL),
        (24, 'گلستان', NULL, NULL),
        (25, 'گیلان', NULL, NULL),
        (26, 'لرستان', NULL, NULL),
        (27, 'مازندران', NULL, NULL),
        (28, 'مرکزی', NULL, NULL),
        (29, 'هرمزگان', NULL, NULL),
        (30, 'همدان', NULL, NULL),
        (31, 'یزد', NULL, NULL);
        ";
        \DB::unprepared($sql) ;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('import_provinces');
    }
}
