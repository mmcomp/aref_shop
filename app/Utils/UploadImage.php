<?php
namespace App\Utils;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;


class UploadImage
{

    public function getImage(object $image, string $image_path, string $prefix="")
    {

        if ($image) {
            $filename = $prefix.now()->timestamp . '.' . $image->extension();
            $path = Storage::putFileAs(
                $image_path, $image, $filename
            );
            return $path = str_replace("public", "storage", $path);
        }
        return null;
    }
    public function createThumbnail(object $image)
    {

        if($image){
            $filename = 'thumbnail_'.now()->timestamp . '.webp';
            $image->storeAs('public/uploads/thumbnails', $filename);
            $thumbnailpath = 'storage/uploads/thumbnails/'.$filename;
            $img = Image::make($thumbnailpath)->resize(300, 300);
            $img->save($thumbnailpath);
            return $thumbnailpath;
        }
        return null;
    }
}