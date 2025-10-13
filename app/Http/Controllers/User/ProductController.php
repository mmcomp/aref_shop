<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetPerPageRequest;
use App\Http\Requests\ProductIndexRequest;
use App\Http\Requests\User\SetQuizReportRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\User\ProductOfUserCollection;
use App\Http\Resources\User\ListOfVideosOfAProductResource;
use App\Http\Resources\User\ListOfVideosOfAProductCollection;
use App\Http\Resources\ProductDetailPackagesCollection;
use App\Http\Resources\ProductDetailPackagesCollectionCollection;
use App\Http\Resources\ProductDetailPackagesResource;
use App\Utils\GetNameOfSessions;
use App\Utils\RaiseError;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Refund;
use App\Models\ProductDetailChair;
use App\Models\ProductDetailPackage;

use App\Http\Resources\UserProductChairsResource;
use App\Http\Resources\GetListOfChairsResource;
use App\Models\Quiz;
use App\Models\User;
use App\Models\UserProduct;
use App\Models\UserQuiz;
use App\Utils\Quiz24Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use DB;
use Illuminate\Support\Facades\Request;
use Log;
use App\Http\Resources\User\QuizzCollection;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \App\Http\Requests\ProductIndexRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(ProductIndexRequest $request)
    {
        $per_page = $request->get('per_page');
        $category_ones_id = $request->input('category_ones_id');
        $category_twos_id = $request->input('category_twos_id');
        $category_threes_id = $request->input('category_threes_id');
        $products = Product::where('is_deleted', false)->where('published', true)->with('userProducts.user')
            ->where(function ($query) use ($category_ones_id, $category_twos_id, $category_threes_id) {
                if ($category_ones_id != null) $query->where('category_ones_id', $category_ones_id);
                if ($category_twos_id != null) $query->where('category_twos_id', $category_twos_id);
                if ($category_threes_id != null) $query->where('category_threes_id', $category_threes_id);
            })
            //->orderBy('id', 'desc');
            ->orderBy('order_date', 'desc');
        if ($per_page == "all") {
            $products = $products->get();
        } else {
            $products = $products->paginate(env('PAGE_COUNT'));
        }

        return (new ProductOfUserCollection($products))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {

        $product = Product::where('is_deleted', false)->where('published', true)->with('productFiles.file')->find($id);
        if ($product != null) {
            return (new ProductResource($product))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new ProductResource($product))->additional([
            'errors' => ['product' => ['Product not found!']],
        ])->response()->setStatusCode(404);
    }
    /**
     * paginate function for array
     *
     * @param array $items
     * @param integer $perPage
     * @param  $page
     * @param array $options
     * @return void
     */
    public function paginate(array $items, $perPage = 5, $page = null, $options = [])
    {

        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
    /**
     * list videos of a product
     *
     * @param  int  $id
     * @param  \App\Http\Requests\GetPerPageRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ListOfVideosOfAProduct(GetPerPageRequest $request, $id)
    {

        $getNameOfSessions = new GetNameOfSessions;
        $per_page = $request->get('per_page');
        $product = Product::where('is_deleted', false)->where('published', true)->with('productDetailVideos.videoSession')->find($id);
        $product_detail_videos = [];
        if ($product != null) {
            $product_detail_videos = $getNameOfSessions->getProductDetailVideos($product, Auth::user()->id);
            $product_detail_video_items = $per_page == "all" ? $product_detail_videos : $this->paginate($product_detail_videos, env('PAGE_COUNT'));
            $productArr = ["name" => $product->name, "thumbnail" => $product->main_image_thumb_path];
            return ((new ListOfVideosOfAProductCollection($product_detail_video_items))->foo($productArr))->additional([
                'errors' => null
            ])->response()->setStatusCode(200);
        }
        return (new ListOfVideosOfAProductResource(null))->additional([
            'errors' => ['product' => ['Product not found!']],
        ])->response()->setStatusCode(404);
    }
    /**
     * get packages of a product that is package
     *
     * @param  int  $id
     * @param  \App\Http\Requests\GetPerPageRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ListOfPackagesOfAProduct(GetPerPageRequest $request, $id)
    {

        $raiseError = new RaiseError;
        $per_page = $request->get('per_page');
        $product = Product::where('is_deleted', false)->where('published', true)->find($id);
        $product_detail_packages = [];
        if ($product != null) {
            $raiseError->ValidationError($product->type != 'package', ['type' => ['You should get a product with type package']]);
            if ($product->productDetailPackages) {
                for ($indx = 0; $indx < count($product->productDetailPackages); $indx++) {
                    $product_detail_packages[] = $product->productDetailPackages[$indx];
                }
            }
            $product_detail_package_items = $per_page == "all" ? $product_detail_packages : $this->paginate($product_detail_packages, env('PAGE_COUNT'));
            return ((new ProductDetailPackagesCollection($product_detail_package_items)))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new ProductDetailPackagesResource(null))->additional([
            'errors' => ['product' => ['Product not found!']],
        ])->response()->setStatusCode(404);
    }

    public function ListOfGroupPackagesOfAProduct(GetPerPageRequest $request, $id)
    {
        $raiseError = new RaiseError;
        $per_page = $request->get('per_page');
        $product = Product::where('is_deleted', false)->where('published', true)->find($id);
        $product_detail_packages = [];
        if ($product != null) {
            $raiseError->ValidationError($product->type != 'package', ['type' => ['You should get a product with type package']]);
            if ($product->productDetailPackages) {
                for ($indx = 0; $indx < count($product->productDetailPackages); $indx++) {
                    $product_detail_packages[] = $product->productDetailPackages[$indx];
                }
            }
            $product_detail_package_items = $per_page == "all" ? $product_detail_packages : $this->paginate($product_detail_packages, env('PAGE_COUNT'));
            $allgroup = [];
            $groups = ProductDetailPackage::groupBy("group")->pluck("group");

            foreach ($groups as $group) {
                $id = 0;
                foreach ($product_detail_package_items as $product_detail_package_item) {
                    if ($product_detail_package_item->group === $group) {
                        $tmpGroup = !isset($group) ? "others" : $group;
                        $allgroup[$tmpGroup][] = $product_detail_package_item;
                        $id++;
                    }
                }
            }
            return ((new  ProductDetailPackagesCollectionCollection($allgroup)))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
    }

    public function ListOfChairsOfAProduct(GetPerPageRequest $request, $id)
    {
        $per_page = request()->get('per_page');
        $product_detail_chairs = ProductDetailChair::where('is_deleted', false)
            ->whereProductsId($id)
            ->orderBy('start', 'asc');
        if ($per_page == "all") {
            $product_detail_chairs = $product_detail_chairs->get();
        } else {
            $product_detail_chairs = $product_detail_chairs->paginate(env('PAGE_COUNT'));
        }
        $reserved_chairs = self::_GetListOfReservedChairs($id);
        $newCollection = [
            'chairs' => $product_detail_chairs,
            'reserved_chairs' => $reserved_chairs
        ];
        return (new UserProductChairsResource($newCollection))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public static function cleanProccessingOrders()
    {
        $fiftheenMinutesAgo = date('Y-m-d H:i:s', strtotime('- 15 minutes'));
        Order::whereStatus('processing')
            ->where('updated_at', '<=', $fiftheenMinutesAgo)
            ->update([
                'status' => 'waiting',
            ]);
    }

    public static function _GetListOfReservedChairs($product_id)
    {
        // $product_tmp=Product::where('id',$product_id)->first();
        // $order_ids_exist=OrderDetail::where('products_id',$product_id)->pluck('orders_id');
        // if($product_tmp->type==="chair"){
        //     $already_canceled=Refund::where('products_id',$product_id)
        //     ->where('is_deleted',0)
        //     ->whereNotIn('orders_id',$order_ids_exist)
        //     ->pluck('id');
        // }


        // $result=OrderDetail::where('products_id',$product_id)
        // ->with('orderChairDetails')
        // ->get()
        // ->map(function ($item, $key) {
        //     return $item->orderChairDetails
        //     ->map(function ($itemChairDetails, $keyChairDetails) {
        //         return $itemChairDetails->chair_number;
        //     });
        // })
        // ->flatten()
        // ->sort();
        //->toArray();



        $result = DB::table('products')
            ->leftjoin('order_details', 'products.id', '=', 'order_details.products_id')
            ->leftjoin('orders', 'orders.id', '=', 'order_details.orders_id')
            ->leftjoin('order_chair_details', 'order_chair_details.order_details_id', '=', 'order_details.id')
            //->select('chair_number')
            ->where('products.id', $product_id)
            ->whereIn('orders.status', ['ok', 'processing'])
            ->orderby('chair_number')
            ->get()
            ->filter(function ($item, $key) {
                return $item->chair_number;
            })
            ->map(function ($item, $key) {
                return $item->chair_number;
            });


        Log::info(json_encode($result));

        return $result;
    }

    public function addUserQuiz($quizId, $userId)
    {
        $userQuiz = UserQuiz::where('user_id', $userId)->where('quiz_id', $quizId)->first();
        if ($userQuiz) {
            return $userQuiz;
        }
        $userQuiz = UserQuiz::create([
            'user_id' => $userId,
            'quiz_id' => $quizId,
            'status' => 'started',
        ]);
        return $userQuiz;
    }

    public function getExamUrlForUser($examCode)
    {
        try {
            $user = Auth::user();
            $res = Quiz24Service::getExamForAUser($user->email, $examCode);
            $url = $res['url'];
            $message = $res['message'];
            if ($url) {
                $this->addUserQuiz($examCode, $user->id);
                return response()->json([
                    'data' => $url,
                    'errors' => null,
                ], 200);
            } else {
                return response()->json([
                    'data' => null,
                    'errors' => ['exam' => [$message]],
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error('getExamUrlForUser error', ['error' => $e->getMessage()]);
            return response()->json([
                'data' => null,
                'errors' => ['exam' => [$e->getMessage()]],
            ], 500);
        }
    }

    public function getQuizProducts()
    {
        $per_page = request()->get('per_page');

        $userQuizzes = UserQuiz::where('user_id', Auth::user()->id);
        $userQuizzes->with('quiz');
        $userQuizzes->orderBy('created_at', 'desc');
        if ($per_page == "all") {
            $userQuizzes = $userQuizzes->get();
        } else {
            $userQuizzes = $userQuizzes->paginate(env('PAGE_COUNT'));
        }
        $quizzes = collect([]);
        foreach ($userQuizzes as $userQuiz) {
            if ($userQuiz->quiz) {
                $quizzes->push($userQuiz->quiz);
            }
        }

        return (new QuizzCollection($quizzes))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function getWeeklyQuizProducts()
    {
        $user = Auth::user();
        $weeklyQuizProducts = Product::where('is_deleted', false)
            ->where('published', true)
            ->where('type', 'quiz24')
            ->whereHas('quizzes', function ($q3) {
                $q3->whereDate('startDateGregorian', '>=', now())
                    ->where('endDateGregorian', '<=', now()->addDays(7));
            })
            ->with('quizzes', function ($q4) {
                $q4->whereDate('startDateGregorian', '>=', now())
                    ->where('endDateGregorian', '<=', now()->addDays(7));
            })
            ->get();
        $userProducts = UserProduct::where('users_id', $user->id)
            ->whereIn('products_id', $weeklyQuizProducts->pluck('id'))
            ->get();
        $weeklyQuizProducts = $weeklyQuizProducts->map(function ($product) use ($userProducts) {
            $product->hasIt = $userProducts->contains('products_id', $product->id) ? true : false;
            return $product;
        });

        return (new ProductOfUserCollection($weeklyQuizProducts))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function getFreeQuiz()
    {
        $freeQuizProducts = Product::where('is_deleted', false)
            ->where('published', true)
            ->where('type', 'quiz24')
            ->where('sale_price', 0)
            ->whereHas('quizzes', function ($q3) {
                $q3->whereDate('startDateGregorian', '>=', now())
                    ->where('endDateGregorian', '<=', now()->addMonth(1));
            })
            ->with('quizzes', function ($q4) {
                $q4->whereDate('startDateGregorian', '>=', now())
                    ->where('endDateGregorian', '<=', now()->addMonth(1));
            })
            ->get();

        return (new ProductOfUserCollection($freeQuizProducts))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }


    public function getWeeklyFreeQuiz()
    {
        $freeQuizProducts = Product::where('is_deleted', false)
            ->where('published', true)
            ->where('type', 'quiz24')
            ->where('sale_price', 0)
            ->whereHas('quizzes', function ($q3) {
                $q3->whereDate('startDateGregorian', '>=', now())
                    ->where('endDateGregorian', '<=', now()->addDays(7));
            })
            ->with('quizzes', function ($q4) {
                $q4->whereDate('startDateGregorian', '>=', now())
                    ->where('endDateGregorian', '<=', now()->addDays(7));
            })
            ->get();

        return (new ProductOfUserCollection($freeQuizProducts))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }


    public function getQuiz24Exams()
    {
        // $page = 1;
        // if (request()->has('page')) {
        //     $page = request()->get('page');
        // }
        // $res = Quiz24Service::getExams(intval($page));
        // return response()->json([
        //     'data' => $res['exams'],
        //     'totalCount' => $res['totalCount'],
        //     'page' => $page,
        // ], 200);

        return Quiz24Service::getAllExams();
    }


    public function getExamResultForUser($examCode)
    {
        $user = Auth::user();
        $res = Quiz24Service::getExamReportForAUser($user->email, $examCode);
        $userQuiz = UserQuiz::where('user_id', $user->id)->where('quiz_id', $examCode)->first();
        if ($userQuiz) {
            $userQuiz->status = 'completed';
            $userQuiz->save();
            $res['report'] = $userQuiz->report;
        }
        $exam = Quiz::where('examCode', $examCode)->first();
        if ($exam) {
            $quiz = Quiz24Service::getAExam($examCode);
            // $quiz = Quiz24Service::getAExam($exam->exam_id);
            Log::info('getExamResultForUser quiz', ['quiz' => $quiz]);
            if (isset($quiz['result'])) {
                (new Quiz())->fromQuiz($quiz['result']);
                $res['quiz'] = $quiz['result'];
            } else {
                return response()->json([
                    'data' => null,
                    'errors' => ['exam' => [$quiz['message']]],
                ], 400);
            }
        }
        return response()->json([
            'data' => $res,
        ], 200);
    }

    public function setUserQuizReport(SetQuizReportRequest $request)
    {
        $user = User::where('email', $request->user_mobile)->first();
        $userQuiz = $this->addUserQuiz($request->examCode, $user->id);
        $userQuiz->report = $request->report;
        $userQuiz->status = 'reported';
        $userQuiz->save();
        return response()->json([
            'data' => $userQuiz,
        ], 200);
    }
}
