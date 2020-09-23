<?php

namespace Kavela\EnhancedImageUploader\Console;

use Illuminate\Console\Command;

class InstallEnhancedImageUploader extends Command
{
    protected $signature   = 'enhanced-image-uploader:install';
    protected $description = 'Install enhanced image uploader package';

    public function handle()
    {
        $this -> info('Installing enhanced image uploader...');
        $this -> info('Publishing configuration...');
        $this -> call('vendor:publish', [
            '--provider' => 'Kavela\EnhancedImageUploader\Providers\ImagesServiceProvider',
            '--tag'      => 'config',
        ]);
        $this -> info('Publishing migrations...');
        $this -> call('vendor:publish', [
            '--provider' => 'Kavela\EnhancedImageUploader\Providers\ImagesServiceProvider',
            '--tag'      => 'migrations',
        ]);
        $this -> info('Enhanced image uploader is installed.');
    }
}
