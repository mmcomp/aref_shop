<?php
namespace App\Utils;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;


class UploadImage
{

    public function getImage(object $image, string $prefix="")
    {

        if ($image) {
            $filename = $prefix.now()->timestamp . '.' . $image->extension();
            $path = Storage::putFileAs(
                'public/uploads', $image, $filename
            );
            return $path = str_replace("public", "storage", $path);
        }
        return null;
    }
    public function createThumbnail(object $image)
    {

        if($image){
            $filename = 'thumbnail_'.now()->timestamp . '.' . $image->extension();
            //$image->storeAs('public/uploads/thumbnails', $filename);
            $thumbnailpath = 'storage/uploads/thumbnails/'.$filename;
            $img = Image::make($thumbnailpath)->resize(300, 300);
            // //dd($img);
            $img->save($thumbnailpath);
            return $thumbnailpath;
        }
        return null;
    }
}
