<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
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
    Route::get('/get/{id}',[UserController::class, 'getUser']);
    Route::post('/edit/{id}', [UserController::class, 'edit']);
    Route::post('/delete/{id}', [UserController::class, 'destroy']);
    Route::post('/set-avatar/{id}',[UserController::class,'setAvatar']);
});
