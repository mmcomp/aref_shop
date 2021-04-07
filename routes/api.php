<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProvinceController;
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
    Route::get('/get/{id}',[UserController::class, 'getUser']);
    Route::post('/edit/{id}', [UserController::class, 'edit']);
    Route::post('/delete/{id}', [UserController::class, 'destroy']);
});
Route::group([
    'middleware' => 'api',
    'prefix' => 'provinces'

], function ($router) {
    Route::get('/', [ProvinceController::class, 'index']);
    Route::post('/add', [ProvinceController::class, 'store']);
    Route::get('/get/{id}',[ProvinceController::class, 'show']);
    Route::get('/get-provinces-of-a-city/{id}',[ProvinceController::class,'getCitiesOfAProvince']);
    Route::put('/edit/{id}', [ProvinceController::class, 'update']);
    Route::delete('/delete/{id}', [ProvinceController::class, 'destroy']);
});
Route::group([
    'middleware' => 'api',
    'prefix' => 'cities'

], function ($router) {
    Route::get('/', [CityController::class, 'index']);
    Route::post('/add', [CityController::class, 'create']);
    Route::get('/getCity/{id}',[CityController::class, 'getCity']);
    Route::put('/edit/{id}', [CityController::class, 'edit']);
    Route::delete('/delete/{id}', [CityController::class, 'destroy']);
});
