<?php

use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\ProductController;
use App\Http\Controllers\User\CategoryOnesController;
use App\Http\Controllers\CategoryTwosController;
use App\Http\Controllers\CategoryThreesController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\TeamUserController;
use App\Http\Controllers\User\TeamUserMemberController;
use App\Http\Controllers\User\TeamUserProductController;
use App\Http\Controllers\User\PaymentController;
use App\Http\Controllers\User\ProductDetailVideosController;
use App\Http\Controllers\User\UserVideoSessionHomeWorkController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\UserDescriptionsController;
use App\Http\Controllers\User\ProductCommentController;
use App\Http\Controllers\User\VideoSessionsController;
use App\Http\Controllers\User\ConferenceUserController;
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

Route::group(['middleware' => 'user'], function () {
    Route::middleware('auth:api')->get('/user', function (Request $request) {
        return $request->user();
    });
    Route::group([
        'middleware' => ['auth:api', 'can:attach-homework'],
        'prefix' => 'homework',

    ], function ($router) {
        Route::post('/concat-homework-to-session/{id}', [UserVideoSessionHomeWorkController::class, 'ConcatHomeWorkToSession']);
        Route::delete('/file/{id}', [UserVideoSessionHomeWorkController::class, 'DeleteHomework']);
        Route::post('/add-description/{id}', [UserVideoSessionHomeWorkController::class, 'addDescription']);
        Route::get('/getAll-homework', [UserVideoSessionHomeWorkController::class, 'getAllHomework']);
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
        Route::get('/getProduct/{id}', [ProductController::class, 'show']);
        Route::get('/get-videos/{id}', [ProductController::class, 'ListOfVideosOfAProduct'])->middleware('can:videosessions-of-user');
        Route::get('/get-packages/{id}', [ProductController::class, 'ListOfPackagesOfAProduct'])->middleware('can:product-packages-of-user');
        Route::get('/get-packages-in-group/{id}', [ProductController::class, 'ListOfGroupPackagesOfAProduct'])->middleware('can:product-packages-of-user');
        Route::get('/get-chairs/{id}', [ProductController::class, 'ListOfChairsOfAProduct'])->middleware('can:videosessions-of-user');
        Route::get('/getallChairs', [ProductController::class, 'GetListOfChairs']);
        Route::get('/get-quiz-products', [ProductController::class, 'getQuizProducts']);
        Route::get('/get-free-quiz', [ProductController::class, 'getFreeQuiz']);
        Route::get('/get-quiz-url/{examCode}', [ProductController::class, 'getExamUrlForUser']);
        Route::get('/quiz24/exams', [ProductController::class, 'getQuiz24Exams']);
        Route::get('/get-quiz-result/{examCode}', [ProductController::class, 'getExamResultForUser']);
    });
    Route::group([
        'middleware' => ['auth:api'],
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
        Route::post('/add-package-product', [CartController::class, 'StoreProductPackage']);
        Route::get('/get-whole-cart', [CartController::class, 'getWholeCart']);
        Route::delete('/destroy-whole-cart', [CartController::class, 'destroyWholeCart']);
        Route::put('/edit/{id}', [CartController::class, 'update']);
        Route::put('/add-coupon', [CartController::class, 'addCouponToTheCart']);
        Route::put('/delete-coupon-from-cart', [CartController::class, 'deleteCouponFromCart']);
        Route::delete('/{id}', [CartController::class, 'destroy']);
        Route::delete('/micro-product/{id}', [CartController::class, 'destroyMicroProduct']);
        Route::delete('/chair/{id}', [CartController::class, 'destroyChairMicroProduct']);
        Route::delete('/chair-product/{productId}/{chairNumber}', [CartController::class, 'destroyChairMicroProductWithChairNumber']);
        Route::get('/complete-buying', [CartController::class, 'completeBuying']);
    });
    Route::group([
        'middleware' => ['auth:api', 'can:order'],
        'prefix' => 'order'
    ], function ($router) {
        Route::get('/get-info-of-an-order/{id}', [OrderController::class, 'getInfoOfAnOrder']);
        Route::get('/show-factors-of-user', [OrderController::class, 'showOrdersOfAuthUser']);
        Route::get('/single-sessions-of-user', [OrderController::class, 'singleSessionsOfAuthUser']);
        Route::get('/complete-courses-of-user', [OrderController::class, 'completeCoursesOfAuthUser']);
        Route::get('/show-student-sessions', [OrderController::class, 'showStudentSessions']);
        Route::get('/show-specific-factor-of-user/{id}', [OrderController::class, 'showSpecificOrderOfAuthUser']);
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
        'middleware' => ['auth:api', 'can:product-detail-video-of-user'],
        'prefix' => 'product-detail-videos',
    ], function ($router) {
        Route::get('/show/{id}', [ProductDetailVideosController::class, 'show']);
    });

    Route::group([
        'prefix' => 'product-detail-videos',
    ], function ($router) {
        Route::get('/get-conference-info/{id}', [ProductDetailVideosController::class, 'conferenceInfo']);
    });

    Route::group([
        'middleware' => ['auth:api'],
        'prefix' => 'conference-users',
    ], function ($router) {
        Route::post('/add-user', [ConferenceUserController::class, 'store']);
    });

    Route::group([
        'middleware' => ['auth:api', 'can:user-description-for-user'],
        'prefix' => 'user-descriptions'
    ], function ($router) {
        Route::get('/', [UserDescriptionsController::class, 'index']);
        Route::post('/add', [UserDescriptionsController::class, 'store']);
        Route::get('/show/{id}', [UserDescriptionsController::class, 'show']);
        Route::put('/edit/{id}', [UserDescriptionsController::class, 'update']);
        Route::delete('/{id}', [UserDescriptionsController::class, 'destroy']);
    });
    Route::group([

        'middleware' => ['auth:api', 'can:product-comment'],
        'prefix' => 'product-comments',
    ], function ($router) {
        Route::get('/', [ProductCommentController::class, 'index']);
        Route::post('/add', [ProductCommentController::class, 'store']);
        Route::get('/show/{id}', [ProductCommentController::class, 'show']);
    });

    Route::group([
        'middleware' => ['auth:api', 'can:sessions'],
        'prefix' => 'sessions',
    ], function ($router) {
        Route::get('/free', [VideoSessionsController::class, 'freeSessions']);
        Route::get('/today', [VideoSessionsController::class, 'todaySessions']);
    });


    Route::group([
        'middleware' => ['auth:api'],
    ], function ($router) {
        Route::get('/team-user', [TeamUserController::class, 'index']);
        Route::post('/team-user', [TeamUserController::class, 'store']);
        Route::put('/team-user{id}', [TeamUserController::class, 'update']);
        Route::delete('/team-user{id}', [TeamUserController::class, 'delete']);
    });
    Route::group([
        'middleware' => ['auth:api']
    ], function ($router) {
        Route::get('/team-user-member', [TeamUserMemberController::class, 'index']);
        Route::post('/team-user-member', [TeamUserMemberController::class, 'store']);
        Route::put('/verify-membership/{id}', [TeamUserMemberController::class, 'update']);
        Route::delete('/team-user-member{id}', [TeamUserMemberController::class, 'delete']);
    });
    Route::group([
        'middleware' => ['auth:api']
    ], function ($router) {
        Route::get('/team-user-product', [TeamUserProductController::class, 'showAll']);
        Route::post('/team-user-product', [TeamUserProductController::class, 'add']);
        Route::put('/team_user-product{id}', [TeamUserProductController::class, 'update']);
        Route::delete('/team-user-product{id}', [TeamUserProductController::class, 'delete']);
    });
});
