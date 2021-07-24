<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryOnesCreateRequest;
use App\Http\Requests\CategoryOnesEditRequest;
use App\Http\Requests\SetImageForCategoryOneRequest;
use App\Http\Resources\CategoryOnesCollection;
use App\Http\Resources\CategoryOnesResource;
use Illuminate\Support\Facades\Storage;
use App\Utils\UploadImage;
use App\Models\CategoryOne;
use Exception;
use Log;

class CategoryOnesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {

        $per_page = request()->get('per_page');
        $category_ones_builder = CategoryOne::where('is_deleted', false)->where('published', 1)->orderBy('ordering', 'asc');
        if ($per_page == "all") {
            $category_ones = $category_ones_builder->get();
        } else {
            $category_ones = $category_ones_builder->paginate(env('PAGE_COUNT'));
        }
        return (new CategoryOnesCollection($category_ones))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\CategoryOnesCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CategoryOnesCreateRequest $request)
    {

        $category_one = CategoryOne::create([
            'name' => $request->name,
            'ordering' => $request->ordering,
            'published' => $request->published
        ]);
        return (new CategoryOnesResource($category_one))->additional([
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

        $category_one = CategoryOne::where('is_deleted', false)->find($id);
        if ($category_one != null) {
            return (new CategoryOnesResource($category_one))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new CategoryOnesResource($category_one))->additional([
            'errors' => ['category_one' => ['CategoryOnes not found!']],
        ])->response()->setStatusCode(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\CategoryOnesEditRequest $request $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(CategoryOnesEditRequest $request, $id)
    {

        $category_one = CategoryOne::where('is_deleted', false)->find($id);
        if ($category_one != null) {
            $category_one->update($request->all());
            return (new CategoryOnesResource(null))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new CategoryOnesResource(null))->additional([
            'errors' => ['category_one' => ['CategoryOnes not found!']],
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
        $category_one = CategoryOne::where('is_deleted', false)->find($id);

        if ($category_one != null) {
            $category_one->is_deleted = 1;
            try {
                $category_one->save();
                return (new CategoryOnesResource(null))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('failed in CategoryOnesController/destory', json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new CategoryOnesResource(null))->additional([
                        'errors' => ['fail' => ['CategoryOnes deleting failed!' . json_encode($e)]],
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new CategoryOnesResource(null))->additional([
                        'errors' => ['fail' => ['CategoryOnes deleting failed!']],
                    ])->response()->setStatusCode(500);
                }

            }
        }
        return (new CategoryOnesResource(null))->additional([
            'errors' => ['category_one' => ['CategoryOnes not found!']],
        ])->response()->setStatusCode(404);
    }
    /**
     * Get subset of categoryOne
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function GetSubsetOfCategoryOne($id)
    {

        $category_one = CategoryOne::where('is_deleted', false)->find($id);
        if ($category_one != null) {
            return (new CategoryOnesCollection($category_one->categoryTwos))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new CategoryOnesResource(null))->additional([
            'errors' => ['category_one' => ['CategoryOnes not found!']],
        ])->response()->setStatusCode(404);

    }
    /** Set image for category-one
     *
     * @param int $id
     * @param \App\Http\Requests\SetImageForCategoryOneRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setImage(SetImageForCategoryOneRequest $request, $id)
    {

        $category_one = CategoryOne::where('is_deleted', false)->find($id);
        if ($category_one != null) {
            $upload_image = new UploadImage;
            $upload_image->imageNullablility($category_one->image_path);
            $category_one->image_path = $upload_image->getImage($request->file('image_path'), 'public/uploads/categoryOneImages');
            try {
                $category_one->save();
                return (new CategoryOnesResource(null))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(201);
            } catch (Exception $e) {
                Log::info("fails in saving image set image in CategoryOnesController " . json_encode($e));
                if (env('APP_ENV') == "development") {
                    return (new CategoryOnesResource(null))->additional([
                        'errors' => ["fail" => ["fails in saving image set image in CategoryOnesController " . json_encode($e)]],
                    ])->response()->setStatusCode(500);
                } elseif (env('APP_ENV') == "production") {
                    return (new CategoryOnesResource(null))->additional([
                        'errors' => ["fail" => ["fails in saving image set image in CategoryOnesController "]],
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new CategoryOnesResource(null))->additional([
            'errors' => ['category_one' => ['CategoryOnes not found!']],
        ])->response()->setStatusCode(404);
    }
    /**  Delete image of category-one
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteImage($id)
    {

        $category_one = CategoryOne::where('is_deleted', false)->find($id);
        if ($category_one != null) {
            $image = str_replace("storage", "public", $category_one->image_path);
            $category_one->image_path = null;
            if (Storage::exists($image)) {
                Storage::delete($image);
                try {
                    $category_one->save();
                    return (new CategoryOnesResource(null))->additional([
                        'errors' => null,
                    ])->response()->setStatusCode(204);
                } catch (Exception $e) {
                    Log::info("fails in deleting image in CategoryOnesController/deleteImage " . json_encode($e));
                    if (env('APP_ENV') == "development") {
                        return (new CategoryOnesResource(null))->additional([
                            'errors' => ["fail" => ["fails in deleting image in CategoryOnesController/deleteImage " . json_encode($e)]],
                        ])->response()->setStatusCode(500);
                    } elseif (env('APP_ENV') == "production") {
                        return (new CategoryOnesResource(null))->additional([
                            'errors' => ["fail" => ["fails in deleting image in CategoryOnesController/deleteImage "]],
                        ])->response()->setStatusCode(500);
                    }
                }
            }
        }
    }

}
