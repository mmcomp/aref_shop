<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductCommentEditRequest;
use App\Http\Requests\ProductCommentIndexRequest;
use App\Http\Requests\ProductCommentSearchVerifiedRequest;
use App\Http\Resources\ProductCommentCollection;
use App\Http\Resources\ProductCommentResource;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductComment;

class ProductCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\ProductCommentIndexRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(ProductCommentIndexRequest $request)
    {

        $product_comments = $request->get('per_page') == "all" ? ProductComment::orderBy('id', 'desc')->get() : ProductComment::orderBy('id', 'desc')->paginate(env('PAGE_COUNT'));
        return (new ProductCommentCollection($product_comments))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\ProductCommentEditRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ProductCommentEditRequest $request, $id)
    {

        $product_comment = ProductComment::find($id);
        $product_comment->verified = $request->input('verified');
        $product_comment->verifier_users_id = Auth::user()->id;
        $product_comment->save();
        return (new ProductCommentResource($product_comment))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {

        $product_comment = ProductComment::find($id);
        if ($product_comment != null) {
            $product_comment->delete();
            return (new ProductCommentResource(null))->additional([
                'errors' => null,
            ])->response()->setStatusCode(204);
        }
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
        $products_id = $request->input('products_id');
        $product_comments_builder = ProductComment::where(function ($query) use ($verified) {
            if ($verified != "all") {
                $query->where('verified', $verified);
            }
        })->where(function ($query) use ($products_id) {
            if ($products_id != null) {
                $query->where('products_id', $products_id);
            }
        });
        $comments = $request->per_page == "all" ? $product_comments_builder->orderBy('created_at', 'desc')->get() : $product_comments_builder->orderBy('created_at', 'desc')->paginate(env('PAGE_COUNT'));
        return (new ProductCommentCollection($comments))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
}
