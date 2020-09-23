<?php

namespace Kavela\EnhancedImageUploader\Traits;

use Kavela\EnhancedImageUploader\Models\Image;

trait HasImages
{
    public function enhancedImageUploaderImages()
    {
        return $this -> morphMany(Image :: class, 'entity');
    }

    public static function bootHasImages()
    {
        static :: deleting(function ($model) {
            $images = $model -> enhancedImageUploaderImages;

            if (! empty($images)) {
                foreach ($images as $image) {
                    $image -> delete();
                }
            }
        });
    }
}
