<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Server\CategoryOnesController;
use App\Http\Controllers\Server\ProductController;

/*
|--------------------------------------------------------------------------
| SERVER Routes
|--------------------------------------------------------------------------
|
| Here is where you can register SERVER routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::group([
    'prefix' => 'category-ones',
], function ($router) {
    Route::get('/', [CategoryOnesController::class, 'index']);
});

Route::group([
    'prefix' => 'products',

], function ($router) {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/getProduct/{id}',[ProductController::class,'show']);
    Route::get('/get-videos/{id}',[ProductController::class, 'ListOfVideosOfAProduct']);
    Route::get('/get-packages/{id}',[ProductController::class, 'ListOfPackagesOfAProduct']);
});
