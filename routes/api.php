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
use App\Http\Controllers\UserProductController;

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
    Route::post('/add', [UserController::class, 'store']);
    Route::get('/get/{id}',[UserController::class, 'show']);
    Route::put('/edit', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
    Route::post('/set-avatar/{id}', [UserController::class, 'setAvatar']);
    Route::delete('/avatar/{id}',[UserController::class, 'deleteAvatar']);
    Route::patch('/bulk-delete', [UserController::class, 'bulkDelete']);
    Route::get('/search',[UserController::class, 'search']);
});
Route::group([
    'middleware' => ['auth:api','can:product'],
    'prefix' => 'products',

], function ($router) {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/getProduct/{id}',[ProductController::class,'show']);
    Route::post('/add', [ProductController::class, 'store']);
    Route::put('/edit/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);
    Route::post('/set-main-image/{id}',[ProductController::class, 'setMainImage']);
    Route::post('/set-second-image/{id}',[ProductController::class, 'setSecondImage']);
    Route::delete('/main-image/{id}',[ProductController::class, 'deleteMainImage']);
    Route::delete('/second-image/{id}',[ProductController::class, 'deleteSecondImage']);
    Route::post('/search',[ProductController::class, 'search']);
    Route::get('/get-videos/{id}',[ProductController::class, 'ListOfVideosOfAProduct']);
    Route::get('/get-packages/{id}',[ProductController::class, 'ListOfPackagesOfAProduct']);
});
Route::group([
    'middleware' => ['auth:api','can:productDetailChair'],
    'prefix' => 'product-detail-chairs',

], function ($router) {
    Route::get('/', [ProductDetailChairsController::class, 'index']);
    Route::post('/add', [ProductDetailChairsController::class, 'store']);
    Route::get('/get-product-detail-chairs/{id}',[ProductDetailChairsController::class, 'show']);
    Route::put('/edit/{id}', [ProductDetailChairsController::class, 'update']);
    Route::delete('/{id}', [ProductDetailChairsController::class, 'destroy']);
});
Route::group([
    'middleware' => ['auth:api','can:productDetailDownload'],
    'prefix' => 'product-detail-downloads',

], function ($router) {
    Route::get('/', [ProductDetailDownloadsController::class, 'index']);
    Route::post('/add', [ProductDetailDownloadsController::class, 'store']);
    Route::get('/show/{id}', [ProductDetailDownloadsController::class, 'show']);
    Route::put('/edit/{id}', [ProductDetailDownloadsController::class, 'update']);
    Route::delete('/{id}', [ProductDetailDownloadsController::class, 'destroy']);
});
Route::group([
    'middleware' => ['auth:api','can:productDetailPackage'],
    'prefix' => 'product-detail-packages',

], function ($router) {
    Route::get('/', [ProductDetailPackagesController::class, 'index']);
    Route::post('/add', [ProductDetailPackagesController::class, 'store']);
    Route::get('/show/{id}', [ProductDetailPackagesController::class, 'show']);
    Route::put('/edit/{id}', [ProductDetailPackagesController::class, 'update']);
    Route::delete('/{id}', [ProductDetailPackagesController::class, 'destroy']);
});
Route::group([
    'middleware' =>['auth:api','can:category-one'],
    'prefix' => 'category-ones',
], function ($router) {
    Route::get('/', [CategoryOnesController::class, 'index']);
    Route::post('/add', [CategoryOnesController::class, 'store']);
    Route::get('/show/{id}', [CategoryOnesController::class, 'show']);
    Route::put('/edit/{id}', [CategoryOnesController::class, 'update']);
    Route::delete('/{id}', [CategoryOnesController::class, 'destroy']);
    Route::get('/get-subset/{id}',[CategoryOnesController::class, 'GetSubsetOfCategoryOne']);
    Route::post('/set-image/{id}', [CategoryOnesController::class, 'setImage']);
    Route::delete('/image/{id}',[CategoryOnesController::class, 'deleteImage']);
});
Route::group([
    'middleware' => ['auth:api','can:productDetailVideo'],
    'prefix' => 'product-detail-videos',
], function ($router) {
    Route::get('/', [ProductDetailVideosController::class, 'index']);
    Route::post('/add', [ProductDetailVideosController::class, 'store']);
    Route::get('/show/{id}', [ProductDetailVideosController::class, 'show']);
    Route::put('/edit/{id}', [ProductDetailVideosController::class, 'update']);
    Route::delete('/{id}', [ProductDetailVideosController::class, 'destroy']);
    Route::post('/assign-video-to-a-product',[ProductDetailVideosController::class,'assignVideoToProduct']);
});
Route::group([
    'middleware' => ['auth:api','can:province'],
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
    'middleware' => ['auth:api','can:city'],
    'prefix' => 'cities',

], function ($router) {
    Route::get('/', [CityController::class, 'index']);
    Route::post('/add', [CityController::class, 'store']);
    Route::get('/getCity/{id}',[CityController::class, 'show']);
    Route::put('/edit/{id}', [CityController::class, 'update']);
    Route::delete('/{id}', [CityController::class, 'destroy']);
});
Route::group([
    'middleware' => ['auth:api','can:group_gate'],
    'prefix' => 'group-gates',

], function ($router) {
    Route::get('/', [GroupGatesController::class, 'index']);
    Route::post('/add', [GroupGatesController::class, 'store']);
    Route::get('/get/{id}', [GroupGatesController::class, 'show']);
    Route::put('/edit/{id}', [GroupGatesController::class, 'update']);
    Route::delete('/{id}', [GroupGatesController::class, 'destroy']);
});
Route::group([
    'middleware' => ['auth:api','can:group'],
    'prefix' => 'groups',
], function ($router) {
    Route::get('/', [GroupController::class, 'index']);
    Route::post('/add', [GroupController::class, 'store']);
    Route::get('/get/{id}', [GroupController::class, 'show']);
    Route::put('/edit/{id}', [GroupController::class, 'update']);
    Route::delete('/{id}', [GroupController::class, 'destroy']);
});
Route::group([
    'middleware' => ['auth:api','can:category-two'],
    'prefix' => 'category-twos'
], function ($router) {
    Route::get('/', [CategoryTwosController::class, 'index']);
    Route::post('/add', [CategoryTwosController::class, 'store']);
    Route::get('/show/{id}', [CategoryTwosController::class, 'show']);
    Route::put('/edit/{id}', [CategoryTwosController::class, 'update']);
    Route::delete('/{id}', [CategoryTwosController::class, 'destroy']);
    Route::get('/get-subset/{id}',[CategoryTwosController::class, 'GetSubsetOfCategoryTwo']);

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
    Route::post('/add-video',[VideoSessionsController::class, 'AddVideosAccordingToUserInputs']);
    Route::post('/add-one-video',[VideoSessionsController::class, 'InsertSingleVideoSession']);
    Route::put('/edit-one-video/{id}',[VideoSessionsController::class, 'EditSingleVideoSession']);
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
    'middleware' => ['auth:api','can:product-comment-admin'],
    'prefix' => 'product-comments',
], function ($router) {
    Route::get('/', [ProductCommentController::class, 'index']);
    Route::put('/edit/{id}', [ProductCommentController::class, 'update']);
    Route::delete('/{id}', [ProductCommentController::class, 'destroy']);
    Route::get('/search',[ProductCommentController::class, 'search']);
});
Route::group([
    'middleware' => ['auth:api','can:admin-order'],
    'prefix' => 'orders',
], function ($router) {
    Route::post('/add', [OrderController::class, 'store']);
    Route::post('/add-orderdetail-product/{orders_id}', [OrderController::class, 'storeProduct']);
    Route::post('/add-micro-product/{orders_id}', [OrderController::class, 'StoreMicroProduct']);
    Route::get('/get-cart/{orders_id}', [OrderController::class, 'getWholeCart']);
    Route::delete('/cart/{orders_id}', [OrderController::class, 'destroyWholeCart']);
    Route::delete('/product/{orders_id}/{order_details_id}', [OrderController::class, 'destroy']);
    Route::put('/add-coupon/{orders_id}', [OrderController::class, 'addCouponToTheCart']);
    Route::put('/delete-coupon/{orders_id}', [OrderController::class, 'deleteCouponFromCart']);
    Route::delete('/micro-product/{orders_id}/{order_details_id}', [OrderController::class, 'destroyMicroProduct']);
    Route::post('/complete-buying/{orders_id}',[OrderController::class, 'completeBuying'] );
});
Route::group([
    'middleware' => ['auth:api','can:report-sale'],
    'prefix' => 'user-products',
], function ($router) {
    Route::post('/report-sale', [UserProductController::class, 'reportSale']);
});

