<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductCommentEditRequest;
use App\Http\Requests\ProductCommentIndexRequest;
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
        
        $product_comments = $request->get('per_page') == "all" ? ProductComment::orderBy('id', 'desc')->get():ProductComment::orderBy('id', 'desc')->paginate(env('PAGE_COUNT')); 
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
        if($product_comment != null) {
            $product_comment->delete();
            return (new ProductCommentResource(null))->additional([
                'errors' => null,
            ])->response()->setStatusCode(204);
        }

    }
}
