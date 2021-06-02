<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllDbMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = "
        SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";
        START TRANSACTION;
        SET time_zone = \"+00:00\";
        CREATE TABLE `category_fours` (
          `id` int NOT NULL,
          `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
        
        CREATE TABLE `category_ones` (
          `id` int NOT NULL,
          `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
        
        CREATE TABLE `category_threes` (
          `id` int NOT NULL,
          `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
        
        CREATE TABLE `category_twos` (
          `id` int NOT NULL,
          `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
        
        CREATE TABLE `cities` (
          `id` int NOT NULL,
          `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `provinces_id` int DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          `is_deleted` tinyint DEFAULT '0'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci ROW_FORMAT=DYNAMIC;
        
        CREATE TABLE `coupons` (
          `id` int NOT NULL,
          `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `amount` int NOT NULL,
          `type` enum('percent','amount') CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `expired_at` date DEFAULT NULL,
          `products_id` int NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
        
        CREATE TABLE `orders` (
          `id` int NOT NULL,
          `users_id` int NOT NULL,
          `amount` int NOT NULL,
          `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `status` enum('ok','waiting','cancel') CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
        
        CREATE TABLE `order_chair_details` (
          `id` int NOT NULL,
          `order_details_id` int NOT NULL,
          `chair_number` int NOT NULL,
          `status` enum('ok','waiting','cancel') CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
        
        CREATE TABLE `order_details` (
          `id` int NOT NULL,
          `orders_id` int NOT NULL,
          `products_id` int NOT NULL,
          `price` int NOT NULL,
          `coupons_id` int NOT NULL,
          `coupos_amount` int NOT NULL,
          `coupons_type` enum('register','forget_pass') CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `users_id` int NOT NULL,
          `all_videos_buy` tinyint(1) NOT NULL DEFAULT '0',
          `status` enum('ok','waiting','cancel') CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
        
        CREATE TABLE `order_video_details` (
          `id` int NOT NULL,
          `order_details_id` int NOT NULL,
          `product_details_videos_id` int NOT NULL,
          `price` int NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
        
        CREATE TABLE `payments` (
          `id` int NOT NULL,
          `orders_id` int NOT NULL,
          `users_id` int NOT NULL,
          `price` int NOT NULL,
          `bank_returned` text CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
        
        CREATE TABLE `products` (
          `id` int NOT NULL,
          `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `short_description` varchar(1500) CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `long_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `price` int NOT NULL DEFAULT '0',
          `sale_price` int DEFAULT NULL,
          `sale_expire` date DEFAULT NULL,
          `video_props` text CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci,
          `category_ones_id` int DEFAULT NULL,
          `category_twos_id` int DEFAULT NULL,
          `category_threes_id` int DEFAULT NULL,
          `category_fours_id` int DEFAULT NULL,
          `main_image_path` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci DEFAULT NULL,
          `main_image_thumb_path` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci DEFAULT NULL,
          `second_image_path` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci DEFAULT NULL,
          `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
          `published` tinyint(1) NOT NULL DEFAULT '1',
          `type` enum('normal','download','chairs','video') CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL DEFAULT 'normal',
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
        
        CREATE TABLE `product_comments` (
          `id` int NOT NULL,
          `products_id` int NOT NULL,
          `users_id` int NOT NULL,
          `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
        
        CREATE TABLE `product_detail_chairs` (
          `id` int NOT NULL,
          `products_id` int NOT NULL,
          `start` int NOT NULL,
          `end` int NOT NULL,
          `price` int NOT NULL,
          `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
        
        CREATE TABLE `product_detail_downloads` (
          `id` int NOT NULL,
          `products_id` int NOT NULL,
          `file_path` varchar(1500) CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
        
        CREATE TABLE `product_detail_packages` (
          `id` int NOT NULL,
          `products_id` int NOT NULL,
          `child_products_id` int NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
        
        CREATE TABLE `product_detail_videos` (
          `id` int NOT NULL,
          `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `start_date` date NOT NULL,
          `start_time` time NOT NULL,
          `end_time` time NOT NULL,
          `teacher_users_id` int DEFAULT NULL,
          `products_id` int NOT NULL,
          `price` int NOT NULL,
          `video_session_type` enum('online','offline') CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `video_link` text CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `is_hidden` tinyint(1) NOT NULL DEFAULT '0',
          `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
        
        CREATE TABLE `product_detail_video_childs` (
          `id` int NOT NULL,
          `product_detail_videos_id` int NOT NULL,
          `product_detail_videos_childs_id` int NOT NULL,
          `saver_users_id` int NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
        
        CREATE TABLE `provinces` (
          `id` int NOT NULL,
          `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci ROW_FORMAT=COMPACT;
        
        CREATE TABLE `sms_validations` (
          `mobile` int NOT NULL,
          `code` int NOT NULL,
          `user_info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `type` enum('register','forget_pass') CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
        
        CREATE TABLE `users` (
          `id` int NOT NULL,
          `user_name` varchar(194) CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `first_name` varchar(194) CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `last_name` varchar(194) CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `avatar_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `referrer_users_id` int NOT NULL,
          `pass` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `pass_txt` varchar(194) CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `adress` text CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `postall` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL,
          `cities_id` int NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
        
        
        ALTER TABLE `category_fours`
          ADD PRIMARY KEY (`id`);
        
        ALTER TABLE `category_ones`
          ADD PRIMARY KEY (`id`);
        
        ALTER TABLE `category_threes`
          ADD PRIMARY KEY (`id`);
        
        ALTER TABLE `category_twos`
          ADD PRIMARY KEY (`id`);
        
        ALTER TABLE `cities`
          ADD PRIMARY KEY (`id`),
          ADD KEY `ostan_idx` (`provinces_id`);
        ALTER TABLE `cities` ADD FULLTEXT KEY `name_txt` (`name`);
        
        ALTER TABLE `coupons`
          ADD PRIMARY KEY (`id`);
        
        ALTER TABLE `orders`
          ADD PRIMARY KEY (`id`);
        
        ALTER TABLE `order_chair_details`
          ADD PRIMARY KEY (`id`);
        
        ALTER TABLE `order_details`
          ADD PRIMARY KEY (`id`);
        
        ALTER TABLE `order_video_details`
          ADD PRIMARY KEY (`id`);
        
        ALTER TABLE `payments`
          ADD PRIMARY KEY (`id`);
        
        ALTER TABLE `products`
          ADD PRIMARY KEY (`id`);
        
        ALTER TABLE `product_comments`
          ADD PRIMARY KEY (`id`);
        
        ALTER TABLE `product_detail_chairs`
          ADD PRIMARY KEY (`id`);
        
        ALTER TABLE `product_detail_downloads`
          ADD PRIMARY KEY (`id`);
        
        ALTER TABLE `product_detail_packages`
          ADD PRIMARY KEY (`id`);
        
        ALTER TABLE `product_detail_videos`
          ADD PRIMARY KEY (`id`);
        
        ALTER TABLE `product_detail_video_childs`
          ADD PRIMARY KEY (`id`);
        
        ALTER TABLE `provinces`
          ADD PRIMARY KEY (`id`);
        ALTER TABLE `provinces` ADD FULLTEXT KEY `name_txt` (`name`);
        
        ALTER TABLE `sms_validations`
          ADD PRIMARY KEY (`mobile`);
        
        ALTER TABLE `users`
          ADD PRIMARY KEY (`id`),
          ADD KEY `user_name` (`user_name`),
          ADD KEY `first_name` (`first_name`),
          ADD KEY `last_name` (`last_name`);
        
        
        ALTER TABLE `category_fours`
          MODIFY `id` int NOT NULL AUTO_INCREMENT;
        
        ALTER TABLE `category_ones`
          MODIFY `id` int NOT NULL AUTO_INCREMENT;
        
        ALTER TABLE `category_threes`
          MODIFY `id` int NOT NULL AUTO_INCREMENT;
        
        ALTER TABLE `category_twos`
          MODIFY `id` int NOT NULL AUTO_INCREMENT;
        
        ALTER TABLE `cities`
          MODIFY `id` int NOT NULL AUTO_INCREMENT;
        
        ALTER TABLE `coupons`
          MODIFY `id` int NOT NULL AUTO_INCREMENT;
        
        ALTER TABLE `orders`
          MODIFY `id` int NOT NULL AUTO_INCREMENT;
        
        ALTER TABLE `order_chair_details`
          MODIFY `id` int NOT NULL AUTO_INCREMENT;
        
        ALTER TABLE `order_details`
          MODIFY `id` int NOT NULL AUTO_INCREMENT;
        
        ALTER TABLE `order_video_details`
          MODIFY `id` int NOT NULL AUTO_INCREMENT;
        
        ALTER TABLE `payments`
          MODIFY `id` int NOT NULL AUTO_INCREMENT;
        
        ALTER TABLE `products`
          MODIFY `id` int NOT NULL AUTO_INCREMENT;
        
        ALTER TABLE `product_comments`
          MODIFY `id` int NOT NULL AUTO_INCREMENT;
        
        ALTER TABLE `product_detail_chairs`
          MODIFY `id` int NOT NULL AUTO_INCREMENT;
        
        ALTER TABLE `product_detail_downloads`
          MODIFY `id` int NOT NULL AUTO_INCREMENT;
        
        ALTER TABLE `product_detail_packages`
          MODIFY `id` int NOT NULL AUTO_INCREMENT;
        
        ALTER TABLE `product_detail_videos`
          MODIFY `id` int NOT NULL AUTO_INCREMENT;
        
        ALTER TABLE `product_detail_video_childs`
          MODIFY `id` int NOT NULL AUTO_INCREMENT;
        
        ALTER TABLE `provinces`
          MODIFY `id` int NOT NULL AUTO_INCREMENT;
        
        ALTER TABLE `sms_validations`
          MODIFY `mobile` int NOT NULL AUTO_INCREMENT;
        
        ALTER TABLE `users`
          MODIFY `id` int NOT NULL AUTO_INCREMENT;
        ";
        ini_set('memory_limit', '-1');
        \DB::unprepared($sql) ;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('all_db');
    }
}
