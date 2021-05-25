<?php

use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\ProductController;
use App\Http\Controllers\CategoryOnesController;
use App\Http\Controllers\CategoryTwosController;
use App\Http\Controllers\CategoryThreesController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth',

], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify', [AuthController::class, 'verify']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
    Route::post('/forget-password', [AuthController::class, 'forgetPassword']);
    Route::post('/verify-forget-password', [AuthController::class, 'verifyForgetPassword']);
    Route::patch('/change-password', [AuthController::class, 'ChangePassword']);
});

Route::group([
    'middleware' => ['auth:api'],
    'prefix' => 'products',

], function ($router) {
    Route::get('/', [ProductController::class, 'index'])->middleware('can:product-of-user');
    Route::get('/getProduct/{id}',[ProductController::class,'show']);
    Route::get('/get-videos/{id}',[ProductController::class, 'ListOfVideosOfAProduct'])->middleware('can:videosessions-of-user');
});
Route::group([
    'middleware' =>['auth:api'],
    'prefix' => 'category-ones',
], function ($router) {
    Route::get('/', [CategoryOnesController::class, 'index']);
});
Route::group([
    'middleware' => ['auth:api'],
    'prefix' => 'category-twos'
], function ($router) {
    Route::get('/', [CategoryTwosController::class, 'index']);
});    
Route::group([
    'middleware' => ['auth:api'],
    'prefix' => 'category-threes'
], function ($router) {
    Route::get('/', [CategoryThreesController::class, 'index']);
});
Route::group([
    'middleware' => ['auth:api'],
    'prefix' => 'coupons'
], function ($router) {
    Route::get('/', [CouponController::class, 'index']);
}); 
Route::group([
    'middleware' => ['auth:api', 'can:cart'],
    'prefix' => 'cart'
], function ($router) {
    Route::get('/', [CartController::class, 'index']);
    Route::post('/add', [CartController::class, 'store']);
    Route::post('/add-micro-product', [CartController::class, 'StoreMicroProduct']);
    Route::put('/add-coupon', [CartController::class, 'addCouponToTheCart']);
    Route::get('/show/{id}', [CartController::class, 'show']);
    Route::get('/getWholeCart', [CartController::class, 'getWholeCart']);
    Route::delete('/destroyWholeCart', [CartController::class, 'destroyWholeCart']);
    Route::put('/edit/{id}', [CartController::class, 'update']);
    Route::put('/delete-coupon-from-cart', [CartController::class, 'deleteCouponFromCart']);
    Route::delete('/delete/{id}', [CartController::class, 'destroy']);
    Route::delete('/delete-micro-product/{id}', [CartController::class, 'destroyMicroProduct']);
});
Route::group([
    'middleware' => ['auth:api'],
    'prefix' => 'provinces',

], function ($router) {
    Route::get('/', [ProvinceController::class, 'index']);
    Route::get('/get-cities-of-a-province/{id}', [ProvinceController::class, 'getCitiesOfAProvince']);
});
Route::group([
    'middleware' => ['auth:api'],
    'prefix' => 'cities',

], function ($router) {
    Route::get('/', [CityController::class, 'index']);
});
Route::group([
    'middleware' => ['auth:api'],
    'prefix' => 'users',

], function ($router) {
    Route::put('/edit', [UserController::class, 'update']);
});
