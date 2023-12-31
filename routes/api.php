<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupGatesController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductDetailChairsController;
use App\Http\Controllers\ProductDetailDownloadsController;
use App\Http\Controllers\ProductDetailPackagesController;
use App\Http\Controllers\ProductDetailVideosController;
use App\Http\Controllers\ProductCommentController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryOnesController;
use App\Http\Controllers\CategoryTwosController;
use App\Http\Controllers\CategoryThreesController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\VideoSessionsController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductFilesController;
use App\Http\Controllers\UserDescriptionsController;
use App\Http\Controllers\VideoSessionFilesController;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\UserProductController;
use App\Http\Controllers\UserVideoSessionPresentController;
use App\Http\Controllers\ShowAllTeamUserController;
use App\Http\Controllers\ConferenceUsersController;
use App\Http\Controllers\ReadingStationAbsentReasonsController;
use App\Http\Controllers\ReadingStationCallsController;
use App\Http\Controllers\ReadingStationController;
use App\Http\Controllers\ReadingStationOffdaysController;
use App\Http\Controllers\ReadingStationPackageController;
use App\Http\Controllers\ReadingStationSlutsController;
use App\Http\Controllers\ReadingStationSlutUsersController;
use App\Http\Controllers\ReadingStationStrikeController;
use App\Http\Controllers\ReadingStationUsersController;

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
    'middleware' => ['auth:api', 'can:ping'],
    'prefix' => 'auth',

], function ($router) {
    Route::get('/ping', [AuthController::class, 'ping']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth',

], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/register-login', [AuthController::class, 'registerLogin']);
    Route::post('/register-with-otp', [AuthController::class, 'registerWithOTP']);
    Route::post('/login-with-otp', [AuthController::class, 'loginWithOTP']);
    Route::post('/verify', [AuthController::class, 'verify']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
    Route::post('/forget-password', [AuthController::class, 'forgetPassword']);
    Route::post('/verify-forget-password', [AuthController::class, 'verifyForgetPassword']);
    Route::get('/synchronize', [AuthController::class, 'synchronizeUsers']);
});
Route::group([
    'middleware' => ['auth:api', 'can:user'],
    'prefix' => 'users',

], function ($router) {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/block/{id}', [UserController::class, 'userBlock']);
    Route::get('/unblock/{id}', [UserController::class, 'userUnblock']);
    Route::get('/show-all-block', [UserController::class, 'showAllUserBlock']);
    Route::post('/add', [UserController::class, 'store']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
    Route::post('/set-avatar/{id}', [UserController::class, 'setAvatar']);
    Route::delete('/avatar/{id}', [UserController::class, 'deleteAvatar']);
    Route::patch('/bulk-delete', [UserController::class, 'bulkDelete']);
});
Route::group([
    'middleware' => ['auth:api', 'can:reading_station'],
    'prefix' => 'users',

], function ($router) {
    Route::get('/search', [UserController::class, 'search']);
    Route::get('/get/{id}', [UserController::class, 'show']);
    Route::post('/{user}/reading-station-user/weekly-program', [ReadingStationSlutUsersController::class, 'store']);
    Route::get('/{user}/reading-station-user/weekly-program', [ReadingStationSlutUsersController::class, 'load']);
    Route::put('/{user}/reading-station-user/weekly-program/next-week-package', [ReadingStationSlutUsersController::class, 'changeNextWeekPackage']);
    Route::post('/{user}/reading-station-user', [ReadingStationUsersController::class, 'store']);
    Route::put('/{user}/reading-station-user', [ReadingStationUsersController::class, 'update']);
    Route::delete('/{user}/reading-station-user/{id}', [ReadingStationUsersController::class, 'destroy']);
    Route::post('/', [UserController::class, 'fullStore']);
    Route::put('/edit', [UserController::class, 'update']);
    Route::get('/reading-station-group', [UserController::class, 'groupIndex']);
    Route::patch('/{user}/disable', [UserController::class, 'disableUser']);
    Route::patch('/{user}/enable', [UserController::class, 'enableUser']);
    Route::get('/{user}/reading-station-user/weekly-program-list', [ReadingStationSlutUsersController::class, 'weeklyProgramList']);
    Route::get('/{user}/reading-station-user/load-weekly-program/{weeklyProgram}', [ReadingStationSlutUsersController::class, 'loadWeeklyProgram']);
    Route::get('/{user}/reading-station-user/load-summary-weekly-program/{weeklyProgram}', [ReadingStationSlutUsersController::class, 'loadSummaryWeeklyProgram']);
    Route::get('/{user}/reading-station-user/load-hours-weekly-program/{weeklyProgram}', [ReadingStationSlutUsersController::class, 'loadHoursWeeklyProgram']);
    Route::get('/{user}/absents', [ReadingStationSlutUsersController::class, 'absents']);
    Route::get('/{user}/lates', [ReadingStationSlutUsersController::class, 'lates']);
    Route::get('/{user}/availables', [ReadingStationSlutUsersController::class, 'availables']);
});
Route::group([
    'middleware' => ['auth:api', 'can:user'],
    'prefix' => 'reading-stations',
], function ($router) {
    Route::post('/{readingStation}/offdays', [ReadingStationOffdaysController::class, 'store']);
    Route::delete('/offdays/{id}', [ReadingStationOffdaysController::class, 'destroy']);
    Route::get('/offdays', [ReadingStationOffdaysController::class, 'index']);

    Route::get('/sluts', [ReadingStationSlutsController::class, 'index']);
    Route::post('/{readingStation}/sluts', [ReadingStationSlutsController::class, 'store']);
    Route::delete('/sluts/{id}', [ReadingStationSlutsController::class, 'destroy']);

    Route::get('/users', [ReadingStationUsersController::class, 'index']);

    Route::post('/', [ReadingStationController::class, 'store']);
    Route::put('/', [ReadingStationController::class, 'update']);
    Route::delete('/{readingStation}', [ReadingStationController::class, 'destroy']);
    Route::get('/test-sms', [ReadingStationController::class, 'testSms']);
    Route::get('/{readingStation}/offdays', [ReadingStationOffdaysController::class, 'oneIndex']);
    Route::get('/{readingStation}/users', [ReadingStationUsersController::class, 'oneIndex']);
});
Route::group([
    'middleware' => ['auth:api', 'can:reading_station'],
    'prefix' => 'reading-stations',

], function ($router) {
    Route::get('/{readingStation}', [ReadingStationController::class, 'findOne']);
    Route::get('/{readingStation}/users', [ReadingStationUsersController::class, 'oneIndex']);
    Route::put('/{readingStation}/users', [ReadingStationUsersController::class, 'bulkUpdate']);
    Route::get('/', [ReadingStationController::class, 'index']);
    Route::get('/{readingStation}/users/slut/{slut}', [ReadingStationUsersController::class, 'oneSlutIndex']);
    Route::patch('/{readingStation}/users/{user}/slut/{slut}', [ReadingStationUsersController::class, 'setUserSlutStatus']);
    Route::post('/{readingStation}/users/{user}', [ReadingStationUsersController::class, 'addAbsentPresent']);
    Route::get('/{readingStation}/exits', [ReadingStationUsersController::class, 'allExit']);
    Route::patch('/{readingStation}/exits/{readingStationAbsentPresent}', [ReadingStationUsersController::class, 'updateExitRecord']);
    Route::get('/{readingStation}/users/small', [ReadingStationUsersController::class, 'oneSmallIndex']);
    Route::get('/{readingStation}/users/absents', [ReadingStationUsersController::class, 'absents']);
    Route::get('/{readingStation}/users/absent-tables', [ReadingStationUsersController::class, 'absentTableNumbers']);
    Route::get('/{readingStation}/sluts', [ReadingStationSlutsController::class, 'oneIndex']);
    Route::post('/{readingStation}/absent-verify', [ReadingStationUsersController::class, 'verfyAbsent']);
    Route::get('/{readingStation}/absent-verify-document/{slutUser}', [ReadingStationUsersController::class, 'getVerfyAbsent']);
    Route::get('/{readingStation}/absent-list', [ReadingStationSlutUsersController::class, 'listAbsentUsers']);
    Route::get('/{readingStation}/none-users', [ReadingStationUsersController::class, 'oneNoneUserIndex']);
});
Route::group([
    'middleware' => ['auth:api', 'can:user'],
    'prefix' => 'reading-station-packages',
], function ($router) {
    Route::post('/', [ReadingStationPackageController::class, 'store']);
    Route::put('/', [ReadingStationPackageController::class, 'update']);
    Route::delete('/{readingStationPackage}', [ReadingStationPackageController::class, 'destroy']);
    Route::get('/{readingStationPackage}', [ReadingStationPackageController::class, 'findOne']);
});
Route::group([
    'middleware' => ['auth:api', 'can:reading_station'],
    'prefix' => 'reading-station-packages',
], function ($router) {
    Route::get('/', [ReadingStationPackageController::class, 'index']);
});
Route::group([
    'middleware' => ['auth:api', 'can:user'],
    'prefix' => 'reading-station-strikes',

], function ($router) {
    Route::post('/', [ReadingStationStrikeController::class, 'store']);
    Route::put('/', [ReadingStationStrikeController::class, 'update']);
    Route::delete('/{readingStationStrike}', [ReadingStationStrikeController::class, 'destroy']);
    Route::get('/', [ReadingStationStrikeController::class, 'index']);
    Route::get('/{readingStationStrike}', [ReadingStationStrikeController::class, 'findOne']);
});
Route::group([
    'middleware' => ['auth:api', 'can:reading_station'],
    'prefix' => 'reading-station-strikes',

], function ($router) {
    Route::get('/', [ReadingStationStrikeController::class, 'index']);
});
Route::group([
    'middleware' => ['auth:api', 'can:reading_station'],
    'prefix' => 'reading-station-strike',

], function ($router) {
    Route::get('/{readingStation}', [ReadingStationStrikeController::class, 'readingStationIndex']);
    Route::post('/{readingStation}', [ReadingStationStrikeController::class, 'addReadingStationUserStrike']);
});
Route::group([
    'middleware' => ['auth:api', 'can:user'],
    'prefix' => 'reading-station-absent-reasons',

], function ($router) {
    Route::post('/', [ReadingStationAbsentReasonsController::class, 'store']);
    Route::put('/', [ReadingStationAbsentReasonsController::class, 'update']);
    Route::delete('/{readingStationAbsentReason}', [ReadingStationAbsentReasonsController::class, 'destroy']);
    Route::get('/{readingStationAbsentReason}', [ReadingStationAbsentReasonsController::class, 'show']);
});
Route::group([
    'middleware' => ['auth:api', 'can:reading_station'],
    'prefix' => 'reading-station-absent-reasons',

], function ($router) {
    Route::get('/', [ReadingStationAbsentReasonsController::class, 'index']);
});

Route::group([
    'middleware' => ['auth:api', 'can:reading_station'],
    'prefix' => 'reading-station-calls',

], function ($router) {
    Route::get('/{readingStation}', [ReadingStationCallsController::class, 'index']);
    Route::post('/{readingStation}/users/{user}/slut/{slut}', [ReadingStationCallsController::class, 'sendCall']);
    Route::patch('/{readingStation}/users/{user}/slut/{slut}', [ReadingStationCallsController::class, 'updateExitSlutId']);
});
Route::group([
    'middleware' => ['auth:api', 'can:product'],
    'prefix' => 'products',

], function ($router) {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/getProduct/{id}', [ProductController::class, 'show']);
    Route::post('/add', [ProductController::class, 'store']);
    Route::put('/edit/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);
    Route::post('/set-main-image/{id}', [ProductController::class, 'setMainImage']);
    Route::post('/set-second-image/{id}', [ProductController::class, 'setSecondImage']);
    Route::delete('/main-image/{id}', [ProductController::class, 'deleteMainImage']);
    Route::delete('/second-image/{id}', [ProductController::class, 'deleteSecondImage']);
    Route::post('/search', [ProductController::class, 'search']);
    Route::get('/get-videos/{id}', [ProductController::class, 'ListOfVideosOfAProduct']);
    Route::get('/get-packages/{id}', [ProductController::class, 'ListOfPackagesOfAProduct']);
    Route::get('/get-packages-in-group/{id}', [ProductController::class, 'listOfPackagesOfProductGroup']);
    Route::get('/get-chairs/{id}', [ProductController::class, 'ListOfChairsOfAProduct']);
});
Route::group([
    'middleware' => ['auth:api', 'can:productDetailChair'],
    'prefix' => 'product-detail-chairs',

], function ($router) {
    Route::get('/', [ProductDetailChairsController::class, 'index']);
    Route::get('/{product_id}', [ProductDetailChairsController::class, 'productIndex']);
    Route::post('/add', [ProductDetailChairsController::class, 'store']);
    Route::get('/get-product-detail-chairs/{id}', [ProductDetailChairsController::class, 'show']);
    Route::put('/edit/{id}', [ProductDetailChairsController::class, 'update']);
    Route::delete('/{id}', [ProductDetailChairsController::class, 'destroy']);
});
Route::group([
    'middleware' => ['auth:api', 'can:productDetailDownload'],
    'prefix' => 'product-detail-downloads',

], function ($router) {
    Route::get('/', [ProductDetailDownloadsController::class, 'index']);
    Route::post('/add', [ProductDetailDownloadsController::class, 'store']);
    Route::get('/show/{id}', [ProductDetailDownloadsController::class, 'show']);
    Route::put('/edit/{id}', [ProductDetailDownloadsController::class, 'update']);
    Route::delete('/{id}', [ProductDetailDownloadsController::class, 'destroy']);
});
Route::group([
    'middleware' => ['auth:api', 'can:productDetailPackage'],
    'prefix' => 'product-detail-packages',

], function ($router) {
    Route::get('/', [ProductDetailPackagesController::class, 'index']);
    Route::post('/add', [ProductDetailPackagesController::class, 'store']);
    Route::get('/show/{id}', [ProductDetailPackagesController::class, 'show']);
    Route::put('/edit/{id}', [ProductDetailPackagesController::class, 'update']);
    Route::delete('/{id}', [ProductDetailPackagesController::class, 'destroy']);
});
Route::group([
    'middleware' => ['auth:api', 'can:category-one'],
    'prefix' => 'category-ones',
], function ($router) {
    Route::get('/', [CategoryOnesController::class, 'index']);
    Route::post('/add', [CategoryOnesController::class, 'store']);
    Route::get('/show/{id}', [CategoryOnesController::class, 'show']);
    Route::put('/edit/{id}', [CategoryOnesController::class, 'update']);
    Route::delete('/{id}', [CategoryOnesController::class, 'destroy']);
    Route::get('/get-subset/{id}', [CategoryOnesController::class, 'GetSubsetOfCategoryOne']);
    Route::post('/set-image/{id}', [CategoryOnesController::class, 'setImage']);
    Route::delete('/image/{id}', [CategoryOnesController::class, 'deleteImage']);
});
Route::group([
    'middleware' => ['auth:api', 'can:productDetailVideo'],
    'prefix' => 'product-detail-videos',
], function ($router) {
    Route::get('/', [ProductDetailVideosController::class, 'index']);
    Route::post('/add', [ProductDetailVideosController::class, 'store']);
    Route::get('/show/{id}', [ProductDetailVideosController::class, 'show']);
    Route::put('/edit/{id}', [ProductDetailVideosController::class, 'update']);
    Route::delete('/{id}', [ProductDetailVideosController::class, 'destroy']);
    Route::post('/assign-video-to-a-product', [ProductDetailVideosController::class, 'assignVideoToProduct']);
    Route::post('/disable', [VideoSessionsController::class, 'disable_chats']);
});
Route::group([
    'middleware' => ['auth:api', 'can:province'],
    'prefix' => 'provinces',

], function ($router) {
    Route::get('/', [ProvinceController::class, 'index']);
    Route::post('/add', [ProvinceController::class, 'store']);
    Route::get('/get/{id}', [ProvinceController::class, 'show']);
    Route::get('/get-cities-of-a-province/{id}', [ProvinceController::class, 'getCitiesOfAProvince']);
    Route::put('/edit/{id}', [ProvinceController::class, 'update']);
    Route::delete('/{id}', [ProvinceController::class, 'destroy']);
});
Route::group([
    'middleware' => ['auth:api', 'can:city'],
    'prefix' => 'cities',

], function ($router) {
    Route::get('/', [CityController::class, 'index']);
    Route::post('/add', [CityController::class, 'store']);
    Route::get('/getCity/{id}', [CityController::class, 'show']);
    Route::put('/edit/{id}', [CityController::class, 'update']);
    Route::delete('/{id}', [CityController::class, 'destroy']);
});
Route::group([
    'middleware' => ['auth:api', 'can:group_gate'],
    'prefix' => 'group-gates',

], function ($router) {
    Route::get('/', [GroupGatesController::class, 'index']);
    Route::post('/add', [GroupGatesController::class, 'store']);
    Route::get('/get/{id}', [GroupGatesController::class, 'show']);
    Route::put('/edit/{id}', [GroupGatesController::class, 'update']);
    Route::delete('/{id}', [GroupGatesController::class, 'destroy']);
});
Route::group([
    'middleware' => ['auth:api', 'can:group'],
    'prefix' => 'groups',
], function ($router) {
    Route::get('/', [GroupController::class, 'index']);
    Route::post('/add', [GroupController::class, 'store']);
    Route::get('/get/{id}', [GroupController::class, 'show']);
    Route::put('/edit/{id}', [GroupController::class, 'update']);
    Route::delete('/{id}', [GroupController::class, 'destroy']);
});
Route::group([
    'middleware' => ['auth:api', 'can:category-two'],
    'prefix' => 'category-twos'
], function ($router) {
    Route::get('/', [CategoryTwosController::class, 'index']);
    Route::post('/add', [CategoryTwosController::class, 'store']);
    Route::get('/show/{id}', [CategoryTwosController::class, 'show']);
    Route::put('/edit/{id}', [CategoryTwosController::class, 'update']);
    Route::delete('/{id}', [CategoryTwosController::class, 'destroy']);
    Route::get('/get-subset/{id}', [CategoryTwosController::class, 'GetSubsetOfCategoryTwo']);
});
Route::group([
    'middleware' => ['auth:api', 'can:category-three'],
    'prefix' => 'category-threes'
], function ($router) {
    Route::get('/', [CategoryThreesController::class, 'index']);
    Route::post('/add', [CategoryThreesController::class, 'store']);
    Route::get('/show/{id}', [CategoryThreesController::class, 'show']);
    Route::put('/edit/{id}', [CategoryThreesController::class, 'update']);
    Route::delete('/{id}', [CategoryThreesController::class, 'destroy']);
});
Route::group([
    'middleware' => ['auth:api', 'can:coupon'],
    'prefix' => 'coupons'
], function ($router) {
    Route::get('/', [CouponController::class, 'index']);
    Route::post('/add', [CouponController::class, 'store']);
    Route::post('/customized-add', [CouponController::class, 'customized_store']);
    Route::get('/show/{id}', [CouponController::class, 'show']);
    Route::put('/edit/{id}', [CouponController::class, 'update']);
    Route::delete('/{id}', [CouponController::class, 'destroy']);
});
Route::group([
    'middleware' => ['auth:api', 'can:video-session'],
    'prefix' => 'video-sessions'
], function ($router) {
    Route::get('/', [VideoSessionsController::class, 'index']);
    Route::post('/add', [VideoSessionsController::class, 'store']);
    Route::get('/show/{id}', [VideoSessionsController::class, 'show']);
    Route::put('/edit/{id}', [VideoSessionsController::class, 'update']);
    Route::delete('/{id}', [VideoSessionsController::class, 'destroy']);
    Route::post('/add-video', [VideoSessionsController::class, 'AddVideosAccordingToUserInputs']);
    Route::post('/add-one-video', [VideoSessionsController::class, 'InsertSingleVideoSession']);
    Route::put('/edit-one-video/{id}', [VideoSessionsController::class, 'EditSingleVideoSession']);
    Route::get('/disabled-video-sessions', [VideoSessionsController::class, 'disabledVideoSessions']);
});
Route::group([
    'middleware' => ['auth:api', 'can:file'],
    'prefix' => 'file'
], function ($router) {
    Route::get('/', [FileController::class, 'index']);
    Route::post('/add', [FileController::class, 'store']);
    Route::get('/show/{id}', [FileController::class, 'show']);
    Route::put('/edit/{id}', [FileController::class, 'update']);
    Route::delete('/{id}', [FileController::class, 'destroy']);
});
Route::group([
    'middleware' => ['auth:api', 'can:product-file'],
    'prefix' => 'product-files'
], function ($router) {
    Route::post('/add', [ProductFilesController::class, 'store']);
    Route::delete('/{id}', [ProductFilesController::class, 'destroy']);
});
Route::group([
    'middleware' => ['auth:api', 'can:video-session-file'],
    'prefix' => 'video-session-files'
], function ($router) {
    Route::post('/add', [VideoSessionFilesController::class, 'store']);
    Route::post('/add-new-by-getting-file-info', [VideoSessionFilesController::class, 'createNewVideoSessionFile']);
    Route::delete('/{id}', [VideoSessionFilesController::class, 'destroy']);
});
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
Route::group([
    'middleware' => ['auth:api', 'can:product-comment-admin'],
    'prefix' => 'product-comments',
], function ($router) {
    Route::get('/', [ProductCommentController::class, 'index']);
    Route::put('/edit/{id}', [ProductCommentController::class, 'update']);
    Route::delete('/{id}', [ProductCommentController::class, 'destroy']);
    Route::get('/search', [ProductCommentController::class, 'search']);
});
Route::group([
    'middleware' => ['auth:api', 'can:admin-order'],
    'prefix' => 'orders',
], function ($router) {
    Route::get('/get-info-of-an-order/{id}', [OrderController::class, 'getInfoOfAnOrder']);
    Route::post('/add', [OrderController::class, 'store'])->name("addOrder");
    Route::post('/add-orderdetail-product/{orders_id}', [OrderController::class, 'storeProduct'])->name("addStoreProduct");
    Route::post('/add-orderdetail-product-bymobilelist', [OrderController::class, 'storeProductByMobileList']);
    Route::post('/add-package-product', [OrderController::class, 'storeProductPackage']);

    Route::post('/add-micro-product/{orders_id}', [OrderController::class, 'StoreMicroProduct']);
    Route::get('/get-cart/{orders_id}', [OrderController::class, 'getWholeCart']);
    Route::delete('/cart/{orders_id}', [OrderController::class, 'destroyWholeCart']);
    Route::delete('/product/{orders_id}/{order_details_id}', [OrderController::class, 'destroy']);
    Route::put('/add-coupon/{orders_id}', [OrderController::class, 'addCouponToTheCart']);
    Route::put('/delete-coupon/{orders_id}', [OrderController::class, 'deleteCouponFromCart']);
    Route::delete('/micro-product/{orders_id}/{order_details_id}', [OrderController::class, 'destroyMicroProduct']);
    Route::delete('/chair/{id}', [OrderController::class, 'destroyChairMicroProduct']);
    Route::post('/complete-buying/{orders_id}', [OrderController::class, 'completeBuying']);
    Route::post('/cancel-buying-product', [OrderController::class, 'cancelBuyingOfAProduct']);
    Route::post('/cancel-buying-micro-product', [OrderController::class, 'cancelBuyingOfAMicroProduct']);
});
Route::get('/publish', function () {
    // ...
    //$values = Redis::hGetAll('user');

    Redis::publish('test-channel', json_encode([
        "Type" => "MESSAGE",
        "Token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTYyNzIxMDQwMywiZXhwIjoxNjI3MjE0MDAzLCJuYmYiOjE2MjcyMTA0MDMsImp0aSI6InVBU2VtTEVWcG1QRTZUcGYiLCJzdWIiOjEsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.vUYPZR5FlT0UIbdL-RJlFssSWC6cPnXODwBUULwzs9E",
        "Data" => [
            "video_sessions_id" => 5,
            "msg" => "dd"
        ]
    ]));
});
Route::get('/new-publish', function () {
    // ...
    //$values = Redis::hGetAll('user');

    Redis::publish('absence-presence-channel', json_encode([
        "type" => "online",
        "product_detail_videos_id" => 21,
        "users_id" => 2
    ]));
});
Route::group([
    'middleware' => ['auth:api', 'can:report-sale'],
    'prefix' => 'user-products',
], function ($router) {
    Route::post('/report-sale', [UserProductController::class, 'reportSale']);
});
// Route::group([
//     'middleware' => ['auth:api','can:admin-order'],
//     'prefix' => 'chats',
// ], function ($router) {
//     Route::post('/disable', [ChatMessageController::class, 'disable_chats']);
//     Route::get('/disabled-video-sessions', [ChatMessageController::class, 'disabledVideoSessions']);
// });
Route::group([
    'middleware' => ['auth:api', 'can:user-video-session-admin'],
    'prefix' => 'user-video-session-presents',
], function ($router) {
    Route::get('/report', [UserVideoSessionPresentController::class, 'report']);
});

Route::group([
    'middleware' => ['auth:api'],
    'prefix' => 'conference-users',
], function ($router) {

    Route::get('/report/{product_detail_videos_id}', [ConferenceUsersController::class, 'showReport']);
    Route::get('/getall', [ConferenceUsersController::class, 'index']);
});
Route::group([
    'middleware' => ['auth:api'],
    'prefix' => 'team-users',
], function ($router) {
    Route::get('/report/all-team', [ShowAllTeamUserController::class, 'index']);
    Route::post('/team-mobile', [ShowAllTeamUserController::class, 'addTeamMember']);
    Route::delete('/team-mobile/{teamUserMemberId}', [ShowAllTeamUserController::class, 'deleteTeamMember']);
    Route::delete('/{teamUserId}', [ShowAllTeamUserController::class, 'deleteTeam']);
});
