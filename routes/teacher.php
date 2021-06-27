<?php 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserDescriptionsController;
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
    'middleware' => ['auth:api', 'can:user-description'],
    'prefix' => 'user-descriptions'
], function ($router) {
    Route::get('/', [UserDescriptionsController::class, 'index']);
    Route::post('/add', [UserDescriptionsController::class, 'store']);
    Route::get('/show/{id}', [UserDescriptionsController::class, 'show']);
    Route::put('/edit/{id}', [UserDescriptionsController::class, 'update']);
    Route::delete('/{id}', [UserDescriptionsController::class, 'destroy']);
});
