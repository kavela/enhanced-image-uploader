# Image fields for Nova apps

This package contains a Nova field to add images to resources.

## Requirements

This Nova field requires PHP 7.2 or higher.

## Installation

You can install this package into a Laravel app that uses [Nova](https://nova.laravel.com) via composer:

```bash
composer require kavela/enhanced-image-uploader
```

```bash
php artisan enhanced-image-uploader:install
```

```bash
php artisan migrate
```

## Usage

To make an Eloquent model imageable follow next steps:

Update `config/enhanced-image-uploader.php` configuration file.

Next add the `Kavela\EnhancedImageUploader\Traits\HasImages` trait to it:

```php
class Project extends Model
{
    use Kavela\EnhancedImageUploader\Traits\HasImages;
    
    ...
}
```

Next you can use the `Kavela\EnhancedImageUploader\Images` field in your Nova resource:

```php
namespace App\Nova;

use Kavela\EnhancedImageUploader\Images;

class Project extends Resource
{
    // ...
    
    public function fields(Request $request)
    {
        return [
            // ...
            
            Images::make('Images'),

            // ...
        ];
    }
}
```

All images will be saved in the `enhanced_image_uploader_images` table.

## Limiting images

You can limit the number of images with `limit()`.

```php
Images::make('Images')->limit($maxNumber),
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email dev.kavelashvili@gmail.com instead of using the issue tracker.

## Credits

- [George Kavelashvili](https://github.com/kavela)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
