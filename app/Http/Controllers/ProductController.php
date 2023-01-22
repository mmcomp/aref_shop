<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetPerPageRequest;
use App\Http\Requests\ProductIndexRequest;
use App\Http\Requests\ProductCreateRequest;
use App\Http\Requests\ProductEditRequest;
use App\Http\Requests\ProductImageRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductVideoCollection;
use App\Http\Resources\ProductVideoResource;
use App\Http\Resources\ProductDetailPackagesCollection;
use App\Http\Resources\ProductDetailPackagesCollectionCollection;

use App\Models\Product;
use App\Models\UserProduct;

use App\Models\ProductDetailPackage;
use App\Models\OrderDetail;
use App\Utils\RaiseError;
use App\Utils\UploadImage;
use App\Utils\GetNameOfSessions;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;
use App\Jobs\SynchronizeProductsWithCrmJob;
use App\Models\ProductDetailChair;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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
        $searchName = $request->input('search_name');
        $products = Product::where('is_deleted', false)
            ->where(function ($query) use ($category_ones_id, $category_twos_id, $category_threes_id, $searchName) {
                if ($category_ones_id != null) $query->where('category_ones_id', $category_ones_id);
                if ($category_twos_id != null) $query->where('category_twos_id', $category_twos_id);
                if ($category_threes_id != null) $query->where('category_threes_id', $category_threes_id);
                if ($searchName != null) $query->where('name', 'like','%'.$searchName.'%');
            })
            ->orderBy('id', 'desc');
        if ($per_page == "all") {
            $products = $products->get();
        } else {
            $products = $products->paginate(env('PAGE_COUNT'));
        }
        return (new ProductCollection($products))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Create and Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\ProductCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ProductCreateRequest $request)
    {

        $product = Product::create($request->all());
        try {
            $product->sale_price = ($request->sale_price == null) ? $request->price : $request->sale_price;
            $product->save();
            SynchronizeProductsWithCrmJob::dispatch($product)->delay(Carbon::now()->addSecond(env('CRM_ADD_PRODUCT_TIMEOUT')));
            return (new ProductResource($product))->additional([
                'errors' => null,
            ])->response()->setStatusCode(201);
        } catch (Exception $e) {
            Log::info("fails in create a new product" . json_encode($e));
            if (env('APP_ENV') == 'development') {
                return (new ProductResource(null))->additional([
                    'errors' => ["fail" => ["fails in create a new product" . json_encode($e)]],
                ])->response()->setStatusCode(500);
            } else if (env('APP_ENV') == 'production') {
                return (new ProductResource(null))->additional([
                    'errors' => ["fail" => ["fails in create a new product"]],
                ])->response()->setStatusCode(500);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {

        $product = Product::where('is_deleted', false)->with('productFiles.file')->find($id);
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
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\ProductEditRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, ProductEditRequest $request)
    {

        $product = Product::where('is_deleted', false)->find($id);
        $raiseError = new RaiseError;
        $sw = 0;
        $ids = [];
        $allIds = [];
        if ($product != null) {
            $product->sale_price = ($request->sale_price == null) ? $request->price : $request->sale_price;
            ProductDetailChair::where('products_id',$id)->update([
                "price" => $product->sale_price
            ]);
            $orderDetails = OrderDetail::get();
            foreach ($orderDetails as $orderDetail) {
                if (isset($orderDetail->order) && $orderDetail->order->status == "ok" && $orderDetail->product->type == "package") {
                    $ids[] = $orderDetail->product->id;
                }
            }
            $child_product_ids = ProductDetailPackage::where('is_deleted', false)->whereIn('products_id', $ids)->pluck('child_products_id')->toArray();
            $allIds = array_merge($ids, $child_product_ids);
            $sw = in_array($id, $allIds) ? 1 : 0;
            $raiseError->ValidationError($sw && $product->type != $request->input('type'), ['type' => ['You should not change the type of product!']]);
            if ($sw && $product->type == $request->input('type')) {
                $product->update($request->except('sale_price', 'type'));
            } else if (!$sw) {
                $product->update($request->except('sale_price'));
            }
            return (new ProductResource(null))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new ProductResource(null))->additional([
            'errors' => ['product' => ['Product not found!']],
        ])->response()->setStatusCode(404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {       
      
       $user_product= UserProduct::where("products_id",$id)->first();     
       if($user_product)
       {       
            return (new ProductResource(null))->additional([
                'errors' => ['fail' => ['you can not delete this product the user buy it before' ]],
            ])->response()->setStatusCode(400);
       }
        $product = Product::where('is_deleted', false)->find($id);
        if ($product != null) {
            $product->is_deleted = 1;
            try {
                  ProductDetailPackage::where("child_products_id",$id)->delete();
                $product->save();
                return (new ProductResource(null))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('fail in ProductController/destroy' . json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new ProductResource(null))->additional([
                        'errors' => ['fail' => ['fail in ProductController/destroy' . json_encode($e)]],
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new ProductResource(null))->additional([
                        'errors' => ['fail' => ['fail in ProductController/destroy']],
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new ProductResource(null))->additional([
            'errors' => ['product' => ['Product not found!']],
        ])->response()->setStatusCode(404);
    }
    /**
     * Set main image for product
     *
     * @param  App\Http\Requests\ProductImageRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function setMainImage(ProductImageRequest $request, $id)
    {

        $product = Product::where('is_deleted', false)->find($id);
        if ($product != null) {
            $upload_image = new UploadImage;
            if ($request->file('main_image_path')) {
                $upload_image->imageNullablility($product->main_image_path);
                $upload_image->imageNullablility($product->main_image_thumb_path);
                $product->main_image_path = $upload_image->getImage($request->file('main_image_path'), "public/uploads", "main");
                $product->main_image_thumb_path = $upload_image->createThumbnail($request->file('main_image_path'));
            }
            try {
                $product->save();
                return (new ProductResource(null))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(201);
            } catch (Exception $e) {
                Log::info("fails in saving main image " . json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new ProductResource(null))->additional([
                        'errors' => ["fail" => ["fails in saving main image" . json_encode($e)]],
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new ProductResource(null))->additional([
                        'errors' => ["fail" => ["fails in saving main image"]],
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new ProductResource(null))->additional([
            'errors' => ['product' => ['Product not found!']],
        ])->response()->setStatusCode(404);
    }
    /**
     * Delete main image for product
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteMainImage($id)
    {

        $product = Product::where('is_deleted', false)->find($id);
        if ($product != null) {
            if ($product->main_image_path != null) {
                $main_image_path = str_replace("storage", "public", $product->main_image_path);
                $main_image_thumb_path = str_replace("storage", "public", $product->main_image_thumb_path);
                $product->main_image_path = null;
                $product->main_image_thumb_path = null;
                if (Storage::exists($main_image_path)) {
                    Storage::delete($main_image_path);
                    Storage::delete($main_image_thumb_path);
                }
            }
            try {
                $product->save();
                return (new ProductResource(null))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('fail in ProductController delete main image' . json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new ProductResource(null))->additional([
                        'errors' => ['fail' => ['fail in ProductController delete main image' . json_encode($e)]],
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new ProductResource(null))->additional([
                        'errors' => ['fail' => ['fail in ProductController destroy main image']],
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new ProductResource(null))->additional([
            'errors' => ['product' => ['Product not found!']],
        ])->response()->setStatusCode(404);
    }
    /**
     * Set second image for product
     *
     * @param  App\Http\Requests\ProductImageRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function setSecondImage(ProductImageRequest $request, $id)
    {

        $product = Product::where('is_deleted', false)->find($id);
        if ($product != null) {
            $upload_image = new UploadImage;
            if ($request->file('second_image_path')) {
                $upload_image->imageNullablility($product->second_image_path);
                $product->second_image_path = $upload_image->getImage($request->file('second_image_path'), "public/uploads", "second");
            }
            try {
                $product->save();
                return (new ProductResource(null))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(201);
            } catch (Exception $e) {
                Log::info("fails in saving image " . json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new ProductResource(null))->additional([
                        'errors' => ["fail" => ["fails in saving second image" . json_encode($e)]],
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new ProductResource(null))->additional([
                        'errors' => ["fail" => ["fails in saving second image"]],
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new ProductResource(null))->additional([
            'errors' => ['product' => ['Product not found!']],
        ])->response()->setStatusCode(404);
    }
    /**
     * Delete second image for product
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteSecondImage($id)
    {

        $product = Product::where('is_deleted', false)->find($id);
        if ($product != null) {
            if ($product->second_image_path != null) {
                $second_image_path = str_replace("storage", "public", $product->second_image_path);
                $product->second_image_path = null;
                if (Storage::exists($second_image_path)) {
                    Storage::delete($second_image_path);
                }
            }
            try {
                $product->save();
                return (new ProductResource(null))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('fail in ProductController delete second image' . json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new ProductResource(null))->additional([
                        'errors' => ['fail' => ['fail in ProductController delete second image' . json_encode($e)]],
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new ProductResource(null))->additional([
                        'errors' => ['fail' => ['fail in ProductController destroy second image']],
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new ProductResource(null))->additional([
            'errors' => ['product' => ['Product not found!']],
        ])->response()->setStatusCode(404);
    }
    /**
     * Search products according to name,type,published_at
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {

        $name = trim($request->name);
        $type = trim($request->type);
        $published = trim($request->published);
        $products_builder = Product::where('is_deleted', false)
            ->where(function ($query) use ($name) {
                if ($name != null) {
                    $query->where('name', 'like', '%' . $name . '%');
                }
            })->where(function ($query) use ($type) {
                if ($type != null) {
                    $query->where('type', 'like', '%' . $type . '%');
                }
            })->where(function ($query) use ($published) {
                if ($published != null) {
                    $query->where('published', $published);
                }
            });
        if ($request->per_page == "all") {
            $products = $products_builder->get();
        } else {
            $products = $products_builder->paginate(env('PAGE_COUNT'));
        }
        return (new ProductCollection($products))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
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
     * @param  \App\Http\Requests\GetPerPageRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ListOfVideosOfAProduct(GetPerPageRequest $request, $id)
    {

        $getNameOfSessions = new GetNameOfSessions;
        $raiseError = new RaiseError;
        $per_page = $request->get('per_page');
        $product = Product::where('is_deleted', false)->with('productDetailVideos.videoSession')->find($id);
        $product_detail_videos = [];
        if ($product != null) {
            $raiseError->ValidationError($product->type != 'video', ['type' => ['You should get a product with type video']]);
            $product_detail_videos = $getNameOfSessions->getProductDetailVideos($product, Auth::user()->id);
            // $numArray = [];
            // $i = 1;
            // for ($indx = 0; $indx < count($product->productDetailVideos); $indx++) {
            //     $v = $product->productDetailVideos[$indx];
            //     $numArray[$v->id] = $v != null && $product->productDetailVideos[$indx]->extraordinary ? 0 : $i;
            //     $i = $numArray[$v->id] ? $i + 1 : $i;
            //     $product_detail_videos[] = $product->productDetailVideos[$indx];
            // }
            $product_detail_video_items = $per_page == "all" ? $product_detail_videos : $this->paginate($product_detail_videos, env('PAGE_COUNT'));
            $productArr = ["name" => $product->name, "thumbnail" => $product->main_image_thumb_path];
            return ((new ProductVideoCollection($product_detail_video_items))->foo($productArr))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new ProductVideoResource(null))->additional([
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
        $product = Product::where('is_deleted', false)->find($id);
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


        //     $product_detail_package_items = $per_page == "all" ? $product_detail_packages : $this->paginate($product_detail_packages, env('PAGE_COUNT'));
        //     $allgroup=[];
        //     $groups= ProductDetailPackage::groupBy("group")->pluck("group");
         
        //    foreach($groups as $group)
        //    {           
        //       $id=0;
        //       foreach($product_detail_package_items as $product_detail_package_item)
        //       {               
        //          if($product_detail_package_item->group===$group)
        //          {  
        //             $tmpGroup= !isset($group) ? "others":$group;                  
        //             $allgroup[$tmpGroup][]=$product_detail_package_item;
        //             $id++;
        //          }
        //       }
        //    }    
            //   return ((new  ProductDetailPackagesCollectionCollection($allgroup)))->additional([
            //       'errors' => null,
            //   ])->response()->setStatusCode(200);  
        }


        return (new ProductDetailPackagesCollection(null))->additional([
            'errors' => ['product' => ['Product not found!']],
        ])->response()->setStatusCode(404);
    }

    public function listOfPackagesOfProductGroup(GetPerPageRequest $request, $id)
    {

        $raiseError = new RaiseError;
        $per_page = $request->get('per_page');
        $product = Product::where('is_deleted', false)->find($id);
        $product_detail_packages = [];
        if ($product != null) {
            $raiseError->ValidationError($product->type != 'package', ['type' => ['You should get a product with type package']]);
            if ($product->productDetailPackages) {
                for ($indx = 0; $indx < count($product->productDetailPackages); $indx++) {
                    $product_detail_packages[] = $product->productDetailPackages[$indx];
                }
            }
            // $product_detail_package_items = $per_page == "all" ? $product_detail_packages : $this->paginate($product_detail_packages, env('PAGE_COUNT'));
            // return ((new ProductDetailPackagesCollection($product_detail_package_items)))->additional([
            //     'errors' => null,
            // ])->response()->setStatusCode(200);


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


        return (new ProductDetailPackagesCollection(null))->additional([
            'errors' => ['product' => ['Product not found!']],
        ])->response()->setStatusCode(404);
    }

    public function ListOfChairsOfAProduct(GetPerPageRequest $request, $id){
        $raiseError = new RaiseError;
        $per_page = $request->get('per_page');
        $product = Product::where('is_deleted', false)->find($id);
        return [];
    }
}
