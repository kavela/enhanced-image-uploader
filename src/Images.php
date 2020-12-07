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

    protected $repeaterIndex = 0;

    protected $method;

    protected $constraints;

    protected $quality;

    protected $format;

    public function __construct($name, $attribute = null, $resolveCallback = null)
    {
        if ('enhanced_image_uploader_images' !== $attribute) {
            $attribute = 'enhanced_image_uploader_images';
        }

        parent :: __construct($name, $attribute, $resolveCallback);

        $this -> hideFromIndex();
        $this -> limit();
        $this -> method();
        $this -> constraints();
        $this -> quality();
        $this -> format();
    }

    public function limit($limit = 100)
    {
        return $this -> withMeta([ 'limit' => $limit ]);
    }

    public function method($method = 'fit')
    {
        $this -> method = $method;

        return $this;
    }

    public function constraints($constraints = [])
    {
        $this -> constraints = $constraints;

        return $this;
    }

    public function quality($quality = 60)
    {
        $this -> quality = $quality;

        return $this;
    }

    public function format($format = 'jpg')
    {
        $this -> format = $format;

        return $this;
    }

    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        if ($request -> exists($requestAttribute)) {
            $modelClass = get_class($model);

            $modelClass :: saved(function ($model) use ($request, $requestAttribute) {
                $files  = $request[ $requestAttribute ];
                $images = $this -> uploadImages($model, $files);

                if ('create' === $request -> editMode) {
                    foreach ($images as $image) {
                        unset($image[ 'id' ]);

                        $model -> enhancedImageUploaderImages() -> create($image);
                    }
                }

                if ('update' === $request -> editMode) {
                    foreach ($images as $image) {
                        $imageModel = $model -> enhancedImageUploaderImages() -> where('id', $image[ 'id' ]);

                        unset($image[ 'id' ]);

                        if ($imageModel -> count()) {
                            $imageModel -> update($image);
                        } else {
                            $imageModel -> create($image);
                        }
                    }
                }
            });
        }
    }

    protected function uploadImages($model, $files)
    {
        $images            = [];
        $modelBaseName     = class_basename(get_class($model));
        $modelSingularName = strtolower(Str :: singular($modelBaseName));
        $modelPluralName   = strtolower(Str :: plural($modelBaseName));
        $directory         = $modelPluralName . '/' . $model -> id;
        $path              = storage_path('app/public/' . $directory);
        $storagePublicDisk = Storage :: disk('public');

        foreach ($files as $file) {
            if (! File :: exists($path)) {
                File :: makeDirectory($path, 0775, true, true);
            }

            $original  = $storagePublicDisk -> putFile($directory, $file[ 'file' ]);
            $extension = $file[ 'file' ] -> getClientOriginalExtension();
            $optimized = $directory . '/' . Str :: random(40) . '.' . $extension;
            $name      = str_replace('.' . $extension, '', $file[ 'file' ] -> getClientOriginalName());

            if ($storagePublicDisk -> copy($original, $optimized)) {
                $modelLayout = 'enhanced-image-uploader.layouts.' . $modelSingularName;
                $repeatable  = config($modelLayout . '.repeatable');

                if ($repeatable && empty(config($modelLayout . '.fields.' . $this -> repeaterIndex))) {
                    $this -> repeaterIndex = 0;
                }

                $config      = config($modelLayout . '.fields.' . $this -> repeaterIndex);
                $width       = $config[ 'dimensions' ][ 'width' ];
                $height      = $config[ 'dimensions' ][ 'height' ];
                $method      = isset($config[ 'method' ]) ? $config[ 'method' ] : $this -> method;
                $constraints = isset($config[ 'constraints' ]) ? $config[ 'constraints' ] : $this -> constraints;
                $quality     = isset($config[ 'quality' ]) ? $config[ 'quality' ] : $this -> quality;
                $format      = isset($config[ 'format' ]) ? $config[ 'format' ] : $this -> format;

                Image :: make($storagePublicDisk -> get($optimized))
                    ->{$method}($width, $height, function ($constraint) use ($constraints) {
                        if (! empty($constraints)) {
                            foreach ($constraints as $constraintsMethod) {
                                $constraint ->{$constraintsMethod}();
                            }
                        }
                    })
                    -> save(storage_path('app/public/' . $optimized), $quality, $format);
            }

            $image = [
                'id'        => $file[ 'id' ],
                'name'      => $name,
                'original'  => $original,
                'optimized' => $optimized,
                'order'     => $file[ 'order' ],
            ];

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
                    'order'     => $image -> order,
                ];
            }
        }

        return $resolvedImages;
    }
}
