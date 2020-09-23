<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rules
    |--------------------------------------------------------------------------
    |
    | Image validation rules.
    |
    */

    'rules' => 'mimes:jpeg,jpg,png|max:2048',

    /*
    |--------------------------------------------------------------------------
    | Layouts
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many models as you wish.
    |
    | Example:
    |
    | // Model name (project, product, etc.)
    | 'model_lowercase_name' => [
    |     // Group of fields should be repeatable or not
    |     'repeatable' => true, // Or false
    |     // Group of fields
    |     'fields'     => [
    |         [
    |             'dimensions' => [
    |                 'width'  => 500, // Or any number
    |                 'height' => 300, // Or any number
    |             ],
    |         ],
    |     ],
    | ]
    |
    */

    'layouts' => [],

];
