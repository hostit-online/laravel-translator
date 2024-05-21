# LaravelTranslations

## Installation, Configuration and Usage

To install run ``composer require hostitonline/laravel-translator``

## What does this package do?

This package will allow seamless translation integration with your laravel project. Most packages require you to define a relationship on your model and then call the relationship to display your translation. This package will remove this requirement to keep your code clean!

## How to use it?

Model:

```php
<?php

namespace App\Models;

use HostitOnline\LaravelTranslator\Traits\Translatable;

class Product extends Model
{
    use Translatable;

    /** @var array|string[]  */
    public array $translatable = [
        'name'
    ];

    protected $fillable = [
        'name'
    ];
}
```

How to create a new translation

```php
$product = Product::create([
    'name' => 'Book'
]);

\HostitOnline\LaravelTranslator\Models\Translations::create([
    'value' => 'Boek',
    'translatable_id' => $product->id,
    'translatable_type' => Product::class,
    'iso_code' => 'nl',
    'translatable_column' => 'name'
]);

\HostitOnline\LaravelTranslator\Models\Translations::create([
    'value' => 'Livre',
    'translatable_id' => $product->id,
    'translatable_type' => Product::class,
    'iso_code' => 'fr',
    'translatable_column' => 'name'
]);
```
This package uses the ``app()->getLocale()`` to get the ISO code. This is usually handled in the middleware to ensure the correct language ill be used.

`GET /products/<id> HEADERS: [Content-Language => FR]`
```php
ProductController {

    public function show(Product $product)
    {
        dump($product->name); // Output: Livre
    }
}
```

## License

Laravel-translator is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).

## Contributing

Please report any issue you find in the issues page. Pull requests are more than welcome.
