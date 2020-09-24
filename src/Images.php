<?php

namespace Kavela\EnhancedImageUploader;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class Images extends Field
{
    public $component = 'enhanced-image-uploader';

    private $repeaterIndex = 0;

    public function __construct($name, $attribute = null, $resolveCallback = null)
    {
        if ('enhanced_image_uploader_images' !== $attribute) {
            $attribute = 'enhanced_image_uploader_images';
        }

        parent :: __construct($name, $attribute, $resolveCallback);

        $this -> hideFromIndex();
        $this -> limit();
    }

    public function limit($limit = PHP_INT_MAX)
    {
        return $this -> withMeta([ 'limit' => $limit ]);
    }

    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        if ($request -> exists($requestAttribute)) {
            $modelClass = get_class($model);

            $modelClass :: saved(function ($model) use ($request, $requestAttribute) {
                $files  = $request[ $requestAttribute ];
                $images = $this -> uploadImages($model, $files, $request -> editMode);

                if ('create' === $request -> editMode) {
                    foreach ($images as $image) {
                        $model -> enhancedImageUploaderImages() -> create($image);
                    }
                }

                if ('update' === $request -> editMode) {
                    foreach ($images as $image) {
                        $imageModel = $model -> enhancedImageUploaderImages() -> where('id', $image[ 'id' ]);

                        if ($imageModel -> count()) {
                            unset($image[ 'id' ]);

                            $imageModel -> update($image);
                        } else {
                            $image[ 'order' ] = $image[ 'id' ];

                            unset($image[ 'id' ]);

                            $imageModel -> create($image);
                        }
                    }
                }
            });
        }
    }

    protected function uploadImages($model, $files, $editMode = 'create')
    {
        $images            = [];
        $modelBaseName     = class_basename(get_class($model));
        $modelSingularName = strtolower(Str :: singular($modelBaseName));
        $modelPluralName   = strtolower(Str :: plural($modelBaseName));
        $directory         = $modelPluralName . '/' . $model -> id;
        $path              = storage_path('app/public/' . $directory);
        $storagePublicDisk = Storage :: disk('public');

        foreach ($files as $key => $file) {
            if (! File :: exists($path)) {
                File :: makeDirectory($path, 0775, true, true);
            }

            $original  = $storagePublicDisk -> putFile($directory, $file);
            $optimized = $directory . '/' . Str :: random(40) . '.' . $file -> getClientOriginalExtension();
            $name      = str_replace('.' . $file -> getClientOriginalExtension(), '', $file -> getClientOriginalName());

            if ($storagePublicDisk -> copy($original, $optimized)) {
                $modelLayout = 'enhanced-image-uploader.layouts.' . $modelSingularName;
                $repeatable  = config($modelLayout . '.repeatable');

                if ($repeatable && empty(config($modelLayout . '.fields.' . $this -> repeaterIndex))) {
                    $this -> repeaterIndex = 0;
                }

                $dimensions = config($modelLayout . '.fields.' . $this -> repeaterIndex . '.dimensions');

                Image :: make($storagePublicDisk -> get($optimized))
                    -> fit($dimensions[ 'width' ], $dimensions[ 'height' ])
                    -> save(storage_path('app/public/' . $optimized));
            }

            $image = [
                'name'      => $name,
                'original'  => $original,
                'optimized' => $optimized,
            ];

            if ('create' === $editMode) {
                $image[ 'order' ] = $key;
            }

            if ('update' === $editMode) {
                $image[ 'id' ] = $key;
            }

            $images[] = $image;

            $this -> repeaterIndex++;
        }

        return $images;
    }

    public function resolveAttribute($resource, $attribute = null)
    {
        $resolvedImages = [];
        $images         = $resource -> enhancedImageUploaderImages;

        if (! is_null($images)) {
            foreach ($images as $image) {
                $resolvedImages[] = [
                    'id'        => $image -> id,
                    'name'      => $image -> name,
                    'original'  => isset($image -> original) ? Storage :: url($image -> original) : null,
                    'optimized' => isset($image -> optimized) ? Storage :: url($image -> optimized) : null,
                ];
            }
        }

        return $resolvedImages;
    }
}
