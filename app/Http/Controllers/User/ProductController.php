<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetPerPageRequest;
use App\Http\Requests\ProductIndexRequest;
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
use App\Models\ProductDetailChair;
use App\Models\ProductDetailPackage;

use App\Http\Resources\UserProductChairsResource;
use App\Http\Resources\GetListOfChairsResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use DB;

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
            ->where(function ($query) use ($category_ones_id, $category_twos_id, $category_threes_id)
            {
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
        if ($product != null) 
        {
            $raiseError->ValidationError($product->type != 'package', ['type' => ['You should get a product with type package']]);
            if ($product->productDetailPackages) {
                for ($indx = 0; $indx < count($product->productDetailPackages); $indx++) {
                    $product_detail_packages[] = $product->productDetailPackages[$indx];
                }
            }
          $product_detail_package_items = $per_page == "all" ? $product_detail_packages : $this->paginate($product_detail_packages, env('PAGE_COUNT'));
          $allgroup=[];
          $groups= ProductDetailPackage::groupBy("group")->pluck("group");
       
         foreach($groups as $group)
         {           
            $id=0;
            foreach($product_detail_package_items as $product_detail_package_item)
            {               
               if($product_detail_package_item->group===$group)
               {  
                  $tmpGroup= !isset($group) ? "others":$group;                  
                  $allgroup[$tmpGroup][]=$product_detail_package_item;
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
        $reserved_chairs =self::_GetListOfReservedChairs($id);
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
        $result = DB::table('products')
            ->leftjoin('order_details','products.id','=','order_details.products_id')
            ->leftjoin('orders','orders.id','=','order_details.orders_id')
            ->leftjoin('order_chair_details','order_chair_details.order_details_id','=','order_details.id')
            ->select('chair_number') 
            ->where('products.id',$product_id)
            ->whereIn('orders.status',['ok', 'processing'])
            ->orderby('chair_number')
            ->get()        
            ->filter(function ($item, $key) {
                return $item->chair_number;
            })
            ->map(function ($item, $key) {
                return $item->chair_number;
            });

        return $result;
    }
}
