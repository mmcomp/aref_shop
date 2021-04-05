<?php
namespace App\Utils;

use Illuminate\Support\Facades\Storage;


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
}
