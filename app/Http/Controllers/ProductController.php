<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListOfVideosOfAProductRequest;
use App\Http\Requests\ProductIndexRequest;
use App\Http\Requests\ProductCreateRequest;
use App\Http\Requests\ProductEditRequest;
use App\Http\Requests\ProductImageRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductVideoCollection;
use App\Http\Resources\ProductVideoResource;
use App\Models\Product;
use App\Models\VideoSession;
use App\Utils\UploadImage;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;
use Log;

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
        //TODO: the code should be optimized...
        if ($per_page == "all") {
            $products = Product::where('is_deleted', false)
            ->where(function($query) use($category_ones_id, $category_twos_id, $category_threes_id){
                if($category_ones_id != null) $query->where('category_ones_id', $category_ones_id); 
                if($category_twos_id != null) $query->where('category_twos_id', $category_twos_id); 
                if($category_threes_id != null) $query->where('category_threes_id', $category_threes_id); 
            })
            ->orderBy('id', 'desc')->get();
        } else {
            $products = Product::where('is_deleted', false)
            ->where(function($query) use($category_ones_id, $category_twos_id, $category_threes_id){
                if($category_ones_id != null) $query->where('category_ones_id', $category_ones_id); 
                if($category_twos_id != null) $query->where('category_twos_id', $category_twos_id); 
                if($category_threes_id != null) $query->where('category_threes_id', $category_threes_id); 
            })
            ->orderBy('id', 'desc')->paginate(env('PAGE_COUNT'));
        }
        return (new ProductCollection($products))->additional([
            'error' => null,
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
            return (new ProductResource($product))->additional([
                'error' => null,
            ])->response()->setStatusCode(201);
        } catch (Exception $e) {
            Log::info("fails in create a new product" . json_encode($e));
            if (env('APP_ENV') == 'development') {
                return (new ProductResource(null))->additional([
                    'error' => "fails in create a new product" . json_encode($e),
                ])->response()->setStatusCode(500);
            } else if (env('APP_ENV') == 'production') {
                return (new ProductResource(null))->additional([
                    'error' => "fails in create a new product",
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

        $product = Product::where('is_deleted', false)->find($id);
        if ($product != null) {
            return (new ProductResource($product))->additional([
                'error' => null,
            ])->response()->setStatusCode(200);
        }
        return (new ProductResource($product))->additional([
            'error' => 'Product not found!',
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
        if ($product != null) {
            $product->sale_price = ($request->sale_price == null) ? $request->price : $request->sale_price;
            $product->update($request->except('sale_price'));

            return (new ProductResource(null))->additional([
                'error' => null,
            ])->response()->setStatusCode(200);
        }
        return (new ProductResource(null))->additional([
            'error' => 'Product not found!',
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
        $product = Product::where('is_deleted', false)->find($id);
        if ($product != null) {
            $product->is_deleted = 1;
            try {
                $product->save();
                return (new ProductResource(null))->additional([
                    'error' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('fail in ProductController/destroy' . json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new ProductResource(null))->additional([
                        'error' => 'fail in ProductController/destroy' . json_encode($e),
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new ProductResource(null))->additional([
                        'error' => 'fail in ProductController/destroy',
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new ProductResource(null))->additional([
            'error' => 'Product not found!',
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
                    'error' => null,
                ])->response()->setStatusCode(201);
            } catch (Exception $e) {
                Log::info("fails in saving main image " . json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new ProductResource(null))->additional([
                        'error' => "fails in saving main image" . json_encode($e),
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new ProductResource(null))->additional([
                        'error' => "fails in saving main image",
                    ])->response()->setStatusCode(500);
                }

            }
        }
        return (new ProductResource(null))->additional([
            'error' => 'Product not found!',
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
                    'error' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('fail in ProductController delete main image' . json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new ProductResource(null))->additional([
                        'error' => 'fail in ProductController delete main image' . json_encode($e),
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new ProductResource(null))->additional([
                        'error' => 'fail in ProductController destroy main image',
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new ProductResource(null))->additional([
            'error' => 'Product not found!',
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
                    'error' => null,
                ])->response()->setStatusCode(201);
            } catch (Exception $e) {
                Log::info("fails in saving image " . json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new ProductResource(null))->additional([
                        'error' => "fails in saving main image" . json_encode($e),
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new ProductResource(null))->additional([
                        'error' => "fails in saving main image",
                    ])->response()->setStatusCode(500);
                }

            }
        }
        return (new ProductResource(null))->additional([
            'error' => 'Product not found!',
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
                    'error' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('fail in ProductController delete second image' . json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new ProductResource(null))->additional([
                        'error' => 'fail in ProductController delete second image' . json_encode($e),
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new ProductResource(null))->additional([
                        'error' => 'fail in ProductController destroy second image',
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new ProductResource(null))->additional([
            'error' => 'Product not found!',
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
            'error' => null,
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
     * @param  \App\Http\Requests\ListOfVideosOfAProductRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ListOfVideosOfAProduct($id)
    {

        $per_page = request()->get('per_page');
        $product = Product::where('is_deleted', false)->with('product_detail_videos.videoSession')->find($id);
        $product_detail_videos = [];
        if ($product != null) {
            $numArray = [];
            $i = 1;
            for($indx = 0;$indx < count($product->product_detail_videos);$indx++) {
                $v = $product->product_detail_videos[$indx]->videoSession;
                $numArray[$v->id] = $v != null && $product->product_detail_videos[$indx]->extraordinary ? 0 : $i;
                $i = $numArray[$v->id] ? $i + 1 : $i;
                $product_detail_videos[] = $product->product_detail_videos[$indx];
            }
            $product_detail_video_items = $per_page == "all" ? $product_detail_videos : $this->paginate($product_detail_videos, env('PAGE_COUNT'));
            return ((new ProductVideoCollection($product_detail_video_items))->foo($numArray))->additional([
                'error' => null,
            ])->response()->setStatusCode(200);
        }
        return (new ProductVideoResource(null))->additional([
            'error' => "Product not found!",
        ])->response()->setStatusCode(404);
    }
}
