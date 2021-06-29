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
use App\Http\Controllers\User\PaymentController;
use App\Http\Controllers\User\UserProductsController;
use App\Http\Controllers\User\ProductDetailVideosController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\ProductCommentController;
use App\Http\Controllers\User\VideoSessionsController;
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
Route::group(['middleware' => 'user'], function(){
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
        Route::get('/get-packages/{id}',[ProductController::class, 'ListOfPackagesOfAProduct'])->middleware('can:product-packages-of-user');
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
        Route::get('/get-whole-cart', [CartController::class, 'getWholeCart']);
        Route::delete('/destroy-whole-cart', [CartController::class, 'destroyWholeCart']);
        Route::put('/edit/{id}', [CartController::class, 'update']);
        Route::put('/add-coupon', [CartController::class, 'addCouponToTheCart']);
        Route::put('/delete-coupon-from-cart', [CartController::class, 'deleteCouponFromCart']);
        Route::delete('/{id}', [CartController::class, 'destroy']);
        Route::delete('/micro-product/{id}', [CartController::class, 'destroyMicroProduct']);
        Route::get('/complete-buying',[CartController::class, 'completeBuying']);
    });
    Route::group([
        'middleware' => ['auth:api', 'can:order'],
        'prefix' => 'order'
    ], function ($router) {
        Route::get('/get-info-of-an-order/{id}',[OrderController::class, 'getInfoOfAnOrder']);
        Route::get('/show-factors-of-user',[OrderController::class, 'showOrdersOfAuthUser']);
        Route::get('/single-sessions-of-user',[OrderController::class, 'singleSessionsOfAuthUser']);
        Route::get('/complete-courses-of-user',[OrderController::class, 'completeCoursesOfAuthUser']);
        Route::get('/show-student-sessions',[OrderController::class, 'showStudentSessions']);
        Route::get('/show-specific-factor-of-user/{id}',[OrderController::class, 'showSpecificOrderOfAuthUser']);
    });
    Route::post('/cart/mellat', [CartController::class, 'mellatBank']);
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
    Route::group([
        'middleware' => ['auth:api', 'can:payment'],
        'prefix' => 'payments'
    ], function ($router) {
        Route::get('/bp-pay-request', [PaymentController::class, 'pay']);
    });
    Route::group([
        'middleware' => ['auth:api','can:product-detail-video-of-user'],
        'prefix' => 'product-detail-videos',
    ], function ($router) {
        Route::get('/show/{id}', [ProductDetailVideosController::class, 'show']);
    });
    Route::group([
        'middleware' => ['auth:api','can:product-comment'],
        'prefix' => 'product-comments',
    ], function ($router) {
        Route::get('/', [ProductCommentController::class, 'index']);
        Route::post('/add', [ProductCommentController::class, 'store']);
        Route::get('/show/{id}', [ProductCommentController::class, 'show']);
    });

    Route::group([
        'middleware' => ['auth:api','can:sessions'],
        'prefix' => 'sessions',
    ], function ($router) {
        Route::get('/free', [VideoSessionsController::class, 'freeSessions']);
        Route::get('/today', [VideoSessionsController::class, 'todaySessions']);
    });
    
});
