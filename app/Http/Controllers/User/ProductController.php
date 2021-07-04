<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetPerPageRequest;
use App\Http\Requests\ProductIndexRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\User\ProductOfUserCollection;
use App\Http\Resources\User\ProductVideoCollection;
use App\Http\Resources\User\ProductVideoResource;
use App\Http\Resources\User\ListOfVideosOfAProductResource;
use App\Http\Resources\User\ListOfVideosOfAProductCollection;
use App\Http\Resources\ProductDetailPackagesCollection;
use App\Utils\Number2Word;
use App\Utils\RaiseError;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

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
        $products = Product::where('is_deleted', false)->with('userProducts.user')
            ->where(function ($query) use ($category_ones_id, $category_twos_id, $category_threes_id) {
                if ($category_ones_id != null) $query->where('category_ones_id', $category_ones_id);
                if ($category_twos_id != null) $query->where('category_twos_id', $category_twos_id);
                if ($category_threes_id != null) $query->where('category_threes_id', $category_threes_id);
            })
            ->orderBy('id', 'desc');
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

        $number = new Number2Word;
        $per_page = $request->get('per_page');
        $product = Product::where('is_deleted', false)->with('productDetailVideos.videoSession')->find($id);
        $product_detail_videos = [];
        if ($product != null) {
            $numArray = [];
            $i = 1;
            for ($indx = 0; $indx < count($product->productDetailVideos); $indx++) {
                $v = $product->productDetailVideos[$indx];
                $numArray[$v->id] = $v != null && $product->productDetailVideos[$indx]->extraordinary ? 0 : $i;
                $i = $numArray[$v->id] ? $i + 1 : $i;
                $product_detail_videos[] = $product->productDetailVideos[$indx];
            }
            for($j = 0; $j < count($product_detail_videos); $j++) {
                $persianAlphabetNum = $number->numberToWords($numArray[$product_detail_videos[$j]->id]);
                if($persianAlphabetNum != null) {
                    $product_detail_videos[$j]->name = $product_detail_videos[$j]->name == null ? (strpos($persianAlphabetNum, "سه") !== false ? str_replace("سه", "سو", $persianAlphabetNum) . 'م' : $persianAlphabetNum . 'م') : $product_detail_videos[$j]->name;
                }
            }
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
        }
        return (new ProductDetailPackagesCollection(null))->additional([
            'errors' => ['product' => ['Product not found!']],
        ])->response()->setStatusCode(404);
    }
}
