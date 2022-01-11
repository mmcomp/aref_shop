<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MenusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $ids = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 15, 16, 17, 18, 19, 20, 21, 22,  100, 101, 102, 103, 104, 105, 106, 107, 23, 108,24];
        $slugs = ['link', 'dropdown', 'link', 'link', 'dropdown', 'link', 'link', 'link', 'dropdown', 'link', 'link', 'dropdown', 'link', 'link', 'link', 'dropdown', 'link', 'link', 'link', 'link', 'link', 'link', 'dropdown', 'link', 'link', 'link', 'link', 'link', 'link','link','link'];
        $names = ['داشبورد', 'کاربران', 'فهرست کاربران', 'تعریف کاربر', 'دسته بندی محصولات', 'دسته سطح یک', 'دسته سطح دو', 'دسته سطح سه', 'محصولات', 'فهرست محصولات', 'تعریف محصول', 'کدهای تخفیف', 'فهرست کدهای تخفیف', 'تعریف کوپن', 'صدور فاکتور', 'گزارش ها', 'مدیریت نظرات', 'گزارش فروش', 'گزارش حضوروغیاب', 'میز کار', 'خرید درس', 'پخش زنده', 'درس های من', 'دوره های کامل من', 'تک جلسات', 'امورمالی', 'جلسات رایگان', 'گزارش همایش ها','تیم من','مدیریت تیم‌ها'];
        $icons = ['fas fa-tachometer-alt', 'fas fa-user', 'fas fa-list', 'fas fa-user-plus', 'fas fa-tag', 'fas fa-list', 'fas fa-list', 'fas fa-list', 'fas fa-shopping-bag', 'fas fa-list', 'fas fa-cart-plus', 'fas fa-percent', 'fas fa-list', 'fas fa-plus', 'fas fa-file-invoice', 'fas fa-chart-bar', 'fas fa-comment', 'fas fa-chart-bar', 'fas fa-chart-bar', 'fas fa-tachometer-alt', 'fas fa-gifts', 'fas fa-video', 'fas fa-graduation-cap', 'fas fa-calendar-alt', 'fas fa-calendar-day', 'fas fa-coins', 'fas fa-play', 'fas fa-chart-bar','fas fa-users','fas fa-users'];
        $hrefs = ['/admin', '/admin/user', '/admin/listusers', '/admin/adduser', '/admin/categories', '/admin/listcategoryones', '/admin/listcategorytwos', '/admin/listcategorythrees', '/admin/product', '/admin/listproducts', '/admin/addproduct', '/admin/discount', '/admin/listcoupons', '/admin/coupons/add', '/admin/addorder', '/admin/reports', '/admin/comments', '/admin/reports', '/admin/presence-report', '/', '/shop', '/lives', '#', '/complete-courses', '/single-sessions', '/orders', '/free', '/admin/conference-report','/my-team','/admin/teams'];
        $parentIds = [null, null, 2, 2, null, 5, 5, 5, null, 9, 9, null, 15, 15, null, null, null, 19, 19, null, null, null, null, 103, 103, null, null, 19,null,null];
        for ($i = 0; $i < count($ids); $i++) {
            DB::table('menus')->insert([
                'id' => $ids[$i],
                'slug' => $slugs[$i],
                'name' => $names[$i],
                'icon' => $icons[$i],
                'href' => $hrefs[$i],
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'parent_id' => $parentIds[$i]
            ]);
        }
    }
}
