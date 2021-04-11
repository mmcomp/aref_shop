<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryOnesController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupGatesController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductDetailChairsController;
use App\Http\Controllers\ProductDetailDownloadsController;
use App\Http\Controllers\ProductDetailPackagesController;
use App\Http\Controllers\ProductDetailVideosController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\UserController;
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
});
Route::group([
    'middleware' => ['auth:api', 'can:user'],
    'prefix' => 'users',

], function ($router) {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/add', [UserController::class, 'create']);
    Route::get('/get/{id}', [UserController::class, 'getUser']);
    Route::put('/edit', [UserController::class, 'edit']);
    Route::delete('/delete/{id}', [UserController::class, 'destroy']);
    Route::post('/set-avatar/{id}', [UserController::class, 'setAvatar']);
    Route::patch('/bulk-delete', [UserController::class, 'bulkDelete']);
});
Route::group([
    'middleware' => ['auth:api','can:product'],
    'prefix' => 'products',

], function ($router) {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/getProduct/{id}', [ProductController::class, 'getProduct']);
    Route::post('/add', [ProductController::class, 'create']);
    Route::post('/edit/{id}', [ProductController::class, 'edit']);
    Route::delete('/delete/{id}', [ProductController::class, 'destroy']);
});
Route::group([
    'middleware' => ['auth:api','can:productDetailChair'],
    'prefix' => 'product-detail-chairs',

], function ($router) {
    Route::get('/', [ProductDetailChairsController::class, 'index']);
    Route::post('/add', [ProductDetailChairsController::class, 'create']);
    Route::get('/get-product-detail-chairs/{id}', [ProductDetailChairsController::class, 'getProductDetailChair']);
    Route::put('/edit/{id}', [ProductDetailChairsController::class, 'edit']);
    Route::delete('/delete/{id}', [ProductDetailChairsController::class, 'destroy']);
});
Route::group([
    'middleware' => ['auth:api','can:productDetailDownload'],
    'prefix' => 'product-detail-downloads',

], function ($router) {
    Route::get('/', [ProductDetailDownloadsController::class, 'index']);
    Route::post('/add', [ProductDetailDownloadsController::class, 'store']);
    Route::get('/show/{id}', [ProductDetailDownloadsController::class, 'show']);
    Route::put('/edit/{id}', [ProductDetailDownloadsController::class, 'update']);
    Route::delete('/delete/{id}', [ProductDetailDownloadsController::class, 'destroy']);
});
Route::group([
    'middleware' => ['auth:api','can:productDetailPackage'],
    'prefix' => 'product-detail-packages',

], function ($router) {
    Route::get('/', [ProductDetailPackagesController::class, 'index']);
    Route::post('/add', [ProductDetailPackagesController::class, 'store']);
    Route::get('/show/{id}', [ProductDetailPackagesController::class, 'show']);
    Route::post('/edit/{id}', [ProductDetailPackagesController::class, 'update']);
    Route::post('/delete/{id}', [ProductDetailPackagesController::class, 'destroy']);
});
Route::group([
    'middleware' =>['auth:api','can:category-one'],
    'prefix' => 'category-ones',
], function ($router) {
    Route::get('/', [CategoryOnesController::class, 'index']);
    Route::post('/add', [CategoryOnesController::class, 'store']);
    Route::get('/show/{id}', [CategoryOnesController::class, 'show']);
    Route::put('/edit/{id}', [CategoryOnesController::class, 'update']);
    Route::delete('/delete/{id}', [CategoryOnesController::class, 'destroy']);
});
Route::group([
    'middleware' => ['auth:api','can:productDetailVideo'],
    'prefix' => 'product-detail-videos',
], function ($router) {
    Route::get('/', [ProductDetailVideosController::class, 'index']);
    Route::post('/add', [ProductDetailVideosController::class, 'store']);
    Route::get('/show/{id}', [ProductDetailVideosController::class, 'show']);
    Route::put('/edit/{id}', [ProductDetailVideosController::class, 'update']);
    Route::delete('/delete/{id}', [ProductDetailVideosController::class, 'destroy']);
});
Route::group([
    'middleware' => ['auth:api','can:province'],
    'prefix' => 'provinces',

], function ($router) {
    Route::get('/', [ProvinceController::class, 'index']);
    Route::post('/add', [ProvinceController::class, 'store']);
    Route::get('/get/{id}', [ProvinceController::class, 'show']);
    Route::get('/get-provinces-of-a-city/{id}', [ProvinceController::class, 'getCitiesOfAProvince']);
    Route::put('/edit/{id}', [ProvinceController::class, 'update']);
    Route::delete('/delete/{id}', [ProvinceController::class, 'destroy']);
});
Route::group([
    'middleware' => ['auth:api','can:city'],
    'prefix' => 'cities',

], function ($router) {
    Route::get('/', [CityController::class, 'index']);
    Route::post('/add', [CityController::class, 'create']);
    Route::get('/getCity/{id}', [CityController::class, 'getCity']);
    Route::put('/edit/{id}', [CityController::class, 'edit']);
    Route::delete('/delete/{id}', [CityController::class, 'destroy']);
});
Route::group([
    'middleware' => 'auth:api',
    'prefix' => 'group-gates',

], function ($router) {
    Route::get('/', [GroupGatesController::class, 'index']);
    Route::post('/add', [GroupGatesController::class, 'store']);
    Route::get('/get/{id}', [GroupGatesController::class, 'show']);
    Route::put('/edit/{id}', [GroupGatesController::class, 'update']);
    Route::delete('/delete/{id}', [GroupGatesController::class, 'destroy']);
});
Route::group([
    'middleware' => ['auth:api','can:group'],
    'prefix' => 'groups',
], function ($router) {
    Route::get('/', [GroupController::class, 'index']);
    Route::post('/add', [GroupController::class, 'store']);
    Route::get('/get/{id}', [GroupController::class, 'show']);
    Route::put('/edit/{id}', [GroupController::class, 'update']);
    Route::delete('/delete/{id}', [GroupController::class, 'destroy']);
});
