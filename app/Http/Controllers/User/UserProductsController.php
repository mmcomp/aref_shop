<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserProduct;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\User\UserProductCollection;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\User\UserProductResource;
use Illuminate\Http\Request;

class UserProductsController extends Controller
{
    
    /**
     * complete courses of authenticated user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeCoursesOfAuthUser()
    {

        $user_id = Auth::user()->id;
        //$user_products = UserProduct::where('users_id', $user_id)->where('partial', 0);
        $user_package_products = UserProduct::where('users_id', $user_id)->where('partial', 0)->whereHas('product', function ($query) {
            $query->where('type', 'package');
            // if('type' == "package") {
            //     dd('hello');
            // }
        })->get();
        dd($user_package_products->join('product_detail_packages', 'product_detail_packages.products_id', '=', 'user_products.products_id'));
        
        //dd(DB::getQueryLog());
        //dd($user_package_products);
        // return (new UserProductCollection($user_products))->additional([
        //     'error' => null,
        // ])->response()->setStatusCode(200);
        
    }
}
