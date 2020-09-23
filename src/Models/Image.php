<?php

namespace Kavela\EnhancedImageUploader\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    protected $table   = 'enhanced_image_uploader_images';
    protected $guarded = [];

    public function entity()
    {
        return $this -> morphTo();
    }

    protected static function boot()
    {
        parent :: boot();

        self :: deleting(function ($image) {
            if (! empty($image -> original)) {
                Storage :: disk('public') -> delete($image -> original);
            }

            if (! empty($image -> optimized)) {
                Storage :: disk('public') -> delete($image -> optimized);
            }
        });
    }
}
