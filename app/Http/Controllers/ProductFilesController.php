<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductFileCreateRequest;
use App\Http\Resources\ProductFileResource;
use App\Models\File;
use App\Models\ProductFile;
use App\Utils\UploadImage;
use Illuminate\Support\Facades\Auth;

class ProductFilesController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\ProductFileCreateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductFileCreateRequest $request)
    {

        $upload_image = new UploadImage;
        $file = File::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'file_path' => $upload_image->getImage($request->file('file'), 'public/uploads/files'),
            'users_id' => Auth::user()->id,
        ]);
        $product_file = ProductFile::updateOrCreate([
            'products_id' => $request->input('products_id'),
            'users_id' => Auth::user()->id,
            'files_id' => $file->id,
        ]);
        return (new ProductFileResource($product_file))->additional([
            'error' => null,
        ])->response()->setStatusCode(201);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $product_file = ProductFile::find($id);
        if ($product_file != null) {
            $product_file->delete();
            return (new ProductFileResource(null))->additional([
                'error' => null,
            ])->response()->setStatusCode(204);
        }
    }
}
