<?php

namespace Kavela\EnhancedImageUploader\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Kavela\EnhancedImageUploader\Console\InstallEnhancedImageUploader;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class ImagesServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this -> publish();

        $this -> registerScriptsAndStyles();

        $this -> app -> booted(function () {
            $this -> routes();
        });

        if ($this -> app -> runningInConsole()) {
            $this -> commands([
                InstallEnhancedImageUploader :: class,
            ]);
        }
    }

    public function register()
    {
        $this -> mergeConfigFrom(
            __DIR__ . '/../../config/enhanced-image-uploader.php',
            'enhanced-image-uploader'
        );
    }

    protected function publish()
    {
        $this -> publishes([
            __DIR__ . '/../../config/enhanced-image-uploader.php' => config_path('enhanced-image-uploader.php'),
        ], 'config');

        $migrationName = date('Y_m_d_His', time()) . '_create_enhanced_image_uploader_images_table.php';

        $this -> publishes([
            __DIR__ . '/../../database/migrations/create_enhanced_image_uploader_images_table.php.stub' =>
                database_path('migrations/' . $migrationName),
        ], 'migrations');
    }

    protected function routes()
    {
        if (! $this -> app -> routesAreCached()) {
            Route :: middleware([ 'nova' ])
                -> prefix('/nova-vendor/enhanced-image-uploader')
                -> group(__DIR__ . '/../../routes/api.php');
        }
    }

    protected function registerScriptsAndStyles()
    {
        Nova :: serving(function (ServingNova $event) {
            Nova :: script('enhanced-image-uploader', __DIR__ . '/../../dist/js/field.js');
            Nova :: style('enhanced-image-uploader', __DIR__ . '/../../dist/css/field.css');
            Nova :: provideToScript([
                'enhancedImageUploader' => [
                    'layouts' => config('enhanced-image-uploader.layouts'),
                ],
            ]);
        });
    }
}
