<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductCommentIndexRequest;
use App\Http\Requests\User\ProductCommentCreateRequest;
use App\Http\Requests\ProductCommentSearchVerifiedRequest;
use App\Http\Resources\User\ProductCommentForUserShowCollection;
use App\Http\Resources\ProductCommentCollection as ResourcesProductCommentCollection;
use App\Http\Resources\User\ProductCommentCollection;
use App\Http\Resources\User\ProductCommentForUserShowResource;
use App\Http\Resources\User\ProductCommentResource;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductComment;
use App\Models\Product;
use Carbon\Carbon;


class ProductCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(ProductCommentIndexRequest $request)
    {
        
        $product_comments = $request->get('per_page') == "all" ? ProductComment::orderBy('id', 'desc')->where('verified', 1)->get():ProductComment::orderBy('id', 'desc')->where('verified', 1)->paginate(env('PAGE_COUNT')); 
        return (new ProductCommentCollection($product_comments))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\ProductCommentCreateRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ProductCommentCreateRequest $request)
    {

        $user_id = Auth::user()->id;
        $products_id = $request->input('products_id');
        $comment = $request->input('comment');
        ProductComment::create([
            'users_id' => $user_id,
            'products_id' => $products_id,
            'comment' => $comment,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        return (new ProductCommentResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
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
        $product_comments = [];
        if($product != null) {
            foreach($product->comments as $product_comment) {
                if($product_comment->verified) {
                    $product_comments[] = $product_comment;
                }
            }
            return (new ProductCommentForUserShowCollection($product_comments))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new ProductCommentForUserShowResource(null))->additional([
            'errors' => ["not_found" => "The product_comments_id does not exist"],
        ])->response()->setStatusCode(404);
    }
     /**
     * filter by verify
     *
     * @param  \Illuminate\Http\ProductCommentSearchVerifiedRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(ProductCommentSearchVerifiedRequest $request)
    {

        $verified = $request->input('verified');
        $product_comments_builder = ProductComment::where('verified', $verified);
        if($verified == 2) {
            $comments = $request->per_page == "all" ? ProductComment::get() : ProductComment::paginate(env('PAGE_COUNT'));
        } else {
            $comments = $request->per_page == "all" ? $product_comments_builder->get() : $product_comments_builder->paginate(env('PAGE_COUNT'));
        }
        return (new ResourcesProductCommentCollection($comments))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);

    }
}
