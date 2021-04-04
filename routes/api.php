<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductDetailChairsController;
use App\Http\Controllers\ProductDetailDownloadsController;
use App\Http\Controllers\ProductDetailPackagesController;
use App\Http\Controllers\UserController;

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
    'prefix' => 'auth'

], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify', [AuthController::class, 'verify']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
    Route::post('/forget-password',[AuthController::class, 'forgetPassword']);
    Route::post('/verify-forget-password',[AuthController::class, 'verifyForgetPassword']);
});
Route::group([
    'middleware' => 'api',
    'prefix' => 'users'

], function ($router) {
    Route::post('/', [UserController::class, 'index']);
    Route::post('/add', [UserController::class, 'create']);
    Route::post('/edit', [UserController::class, 'edit']);
    Route::post('/delete', [UserController::class, 'destroy']);
});
Route::group([
    'middleware' => 'api',
    'prefix' => 'products'

], function ($router) {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/getProduct/{id}',[ProductController::class,'getProduct']);
    Route::post('/add', [ProductController::class, 'create']);
    Route::post('/edit/{id}', [ProductController::class, 'edit']);
    Route::post('/delete/{id}', [ProductController::class, 'destroy']);
});
Route::group([
    'middleware' => 'api',
    'prefix' => 'product-detail-chairs'

], function ($router) {
    Route::get('/', [ProductDetailChairsController::class, 'index']);
    Route::post('/add', [ProductDetailChairsController::class, 'create']);
    Route::get('/get-product-detail-chairs/{id}',[ProductDetailChairsController::class, 'getProductDetailChair']);
    Route::post('/edit/{id}', [ProductDetailChairsController::class, 'edit']);
    Route::post('/delete/{id}', [ProductDetailChairsController::class, 'destroy']);
});
Route::group([
    'middleware' => 'api',
    'prefix' => 'product-detail-downloads'

], function ($router) {
    Route::get('/', [ProductDetailDownloadsController::class, 'index']);
    Route::post('/add', [ProductDetailDownloadsController::class, 'store']);
    Route::get('/show/{id}',[ProductDetailDownloadsController::class, 'show']);
    Route::post('/edit/{id}', [ProductDetailDownloadsController::class, 'update']);
    Route::post('/delete/{id}', [ProductDetailDownloadsController::class, 'destroy']);
});
Route::group([
    'middleware' => 'api',
    'prefix' => 'product-detail-packages'

], function ($router) {
    Route::get('/', [ProductDetailPackagesController::class, 'index']);
    Route::post('/add', [ProductDetailPackagesController::class, 'store']);
    Route::get('/show/{id}',[ProductDetailPackagesController::class, 'show']);
    Route::post('/edit/{id}', [ProductDetailPackagesController::class, 'update']);
    Route::post('/delete/{id}', [ProductDetailPackagesController::class, 'destroy']);
});
