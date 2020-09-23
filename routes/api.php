<?php

use Illuminate\Support\Facades\Route;
use Kavela\EnhancedImageUploader\Http\Controllers\ImagesController;

Route :: delete('images/{image}', [ ImagesController :: class, 'destroy' ]);
Route :: delete('images/{image}/soft-delete', [ ImagesController :: class, 'softDelete' ]);
