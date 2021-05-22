<?php
namespace App\Utils;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class UploadImage
{

    /**
     * helpery function for check image nullablity
     *
     */
    public function imageNullablility($image_path)
    {
        if ($image_path != null) {
            $main_image = str_replace("storage", "public", $image_path);
            if (Storage::exists($main_image)) {
                Storage::delete($main_image);
            }
        }
    }
    public function getImage(object $image, string $image_path, string $prefix = "")
    {

        if ($image) {
            $filename = $prefix . now()->timestamp . '.' . $image->extension();
            $path = Storage::putFileAs(
                $image_path, $image, $filename
            );
            return $path = str_replace("public", "storage", $path);
        }
        return null;
    }
    public function createThumbnail(object $image)
    {

        if ($image) {
            $filename = 'thumbnail_' . now()->timestamp . '.webp';
            $image->storeAs('public/uploads/thumbnails', $filename);
            $thumbnailpath = 'storage/uploads/thumbnails/' . $filename;
            $img = Image::make($thumbnailpath)->fit(300);
            $img->save($thumbnailpath);
            return $thumbnailpath;
        }
        return null;
    }
}
