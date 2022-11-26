<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Teacher\UserDescriptionsController;
use App\Http\Controllers\Teacher\ProductController;
use App\Http\Controllers\Teacher\ProductDetailVideosController;
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

Route::group([
    'middleware' => ['auth:api', 'can:user-description-for-teacher'],
    'prefix' => 'user-descriptions'
], function ($router) {
    Route::get('/', [UserDescriptionsController::class, 'index']);
    Route::post('/add', [UserDescriptionsController::class, 'store']);
    Route::get('/show/{id}', [UserDescriptionsController::class, 'show']);
    Route::put('/edit/{id}', [UserDescriptionsController::class, 'update']);
    Route::delete('/{id}', [UserDescriptionsController::class, 'destroy']);
});
Route::group([
    'middleware' => ['auth:api'],
    'prefix' => 'products',

], function ($router) {
    Route::get('/', [ProductController::class, 'index'])->middleware('can:product-of-teacher');
    //Route::get('/getProduct/{id}',[ProductController::class,'show'])->middleware('can:get-a-product-by-teacher');
    // Route::get('/get-videos/{id}',[ProductController::class, 'ListOfVideosOfAProduct'])->middleware('can:videosessions-of-user');
    // Route::get('/get-packages/{id}',[ProductController::class, 'ListOfPackagesOfAProduct'])->middleware('can:product-packages-of-user');
    // Route::get('/get-packages-in-group/{id}',[ProductController::class, 'ListOfGroupPackagesOfAProduct'])->middleware('can:product-packages-of-user');
    // Route::get('/get-chairs/{id}',[ProductController::class, 'ListOfChairsOfAProduct'])->middleware('can:videosessions-of-user');
    // Route::get('/getallChairs',[ProductController::class, 'GetListOfChairs']);
});
Route::group([
    'middleware' => ['auth:api',/*'can:product-detail-video-of-user'*/],
    'prefix' => 'product-detail-videos',
], function ($router) {
    Route::get('/show/{id}', [ProductDetailVideosController::class, 'show']);
    Route::get('/get_one_by_product_id/{id}', [ProductDetailVideosController::class, 'getOne']);
});
