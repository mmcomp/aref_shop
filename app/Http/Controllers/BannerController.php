<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use App\Http\Resources\BannerCollection;
use App\Http\Resources\BannerResource;
use App\Http\Requests\CreateBannerRequest;
use App\Http\Requests\UpdateBannerRequest;
use App\Utils\UploadImage;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $sort = "id";
        $sort_dir = "desc";
        $banners = Banner::query();
        if ($request->get('sort_dir') != null && $request->get('sort') != null) {
            $sort = $request->get('sort');
            $sort_dir = $request->get('sort_dir');
        }
        $banners->orderBy($sort, $sort_dir);
        if ($request->get('per_page') == "all") {
            $banners = $banners->get();
        } else {
            $banners = $banners->paginate(env('PAGE_COUNT'));
        }
        return (new BannerCollection($banners))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function store(CreateBannerRequest $request)
    {

        $banner = Banner::create($request->all());
        $upload_image = new UploadImage;
        $banner->desktop_image = $upload_image->getImage($request->file('desktop_image'), "public/uploads", "desktop");
        $banner->mobile_image = $upload_image->getImage($request->file('mobile_image'), "public/uploads", "mobile");
        $banner->save();
        return (new BannerResource($banner))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }

    public function show($id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return response()->json([
                'errors' => 'Banner not found',
            ], 404);
        }
        return (new BannerResource($banner))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function update(Request $request, $id)
    {
        $banner = Banner::find($id);
        $banner->update($request->all());
        $upload_image = new UploadImage;
        if ($request->file('desktop_image')) {
            $upload_image->imageNullablility($banner->desktop_image);
            $banner->desktop_image = $upload_image->getImage($request->file('desktop_image'), "public/uploads", "desktop");
        }
        if ($request->file('mobile_image')) {
            $upload_image->imageNullablility($banner->mobile_image);
            $banner->mobile_image = $upload_image->getImage($request->file('mobile_image'), "public/uploads", "mobile");
        }
        $banner->save();
        return (new BannerResource($banner))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function setActive($id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return response()->json([
                'errors' => 'Banner not found',
            ], 404);
        }
        $banner->is_active = $banner->is_active == 1 ? 0 : 1;
        $banner->save();
        return (new BannerResource($banner))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function destroy($id)
    {
        $banner = Banner::find($id);
        $banner->delete();
        return (new BannerResource($banner))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
}
