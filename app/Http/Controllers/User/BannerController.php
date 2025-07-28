<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Http\Resources\User\BannerUserResource;

class BannerController extends Controller
{
    public function getActiveBanners()
    {
        $banner = Banner::where('is_active', true)->first();
        if (!$banner) {
            return response()->json([
                'errors' => null,
                'data' => null,
            ], 200);
        }
        return (new BannerUserResource($banner))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
}
