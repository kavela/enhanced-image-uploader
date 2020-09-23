<?php

namespace Kavela\EnhancedImageUploader\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Kavela\EnhancedImageUploader\Models\Image;

class ImagesController extends Controller
{
    public function softDelete(Request $request, Image $imageModel)
    {
        $image = $imageModel -> findOrFail($request -> image);

        if (! empty($image -> original)) {
            Storage :: disk('public') -> delete($image -> original);
        }

        if (! empty($image -> optimized)) {
            Storage :: disk('public') -> delete($image -> optimized);
        }

        $image -> update([
            'name'      => null,
            'original'  => null,
            'optimized' => null,
        ]);

        return response() -> json([ 'deleted' => true ]);
    }

    public function destroy(Request $request)
    {
        Image :: destroy($request -> image);

        return response() -> json([ 'deleted' => true ]);
    }
}
