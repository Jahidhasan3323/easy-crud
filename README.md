<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://packagist.org/packages/jahid/laravel-easy-crud"><img src="https://img.shields.io/packagist/dt/jahid/laravel-easy-crud" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/jahid/laravel-easy-crud" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/jahid/laravel-easy-crud"><img src="https://img.shields.io/packagist/l/jahid/laravel-easy-crud" alt="License"></a>
</p>

## About Easy CRUD
This package is help you to create a simple CRUD operation. After run the artisan command it create a Model, Controller, Route, Request, Migration files. It also writes code in controller for data Retrieve, Store, View, Update and Delete operation.

You just need to define your column in migration and also define rules for validation in request file.

You have to define blade file as you need (I am working on it, You will get it soon). But you don't need anything for api 🙂

If you find any issue please create an issue, I will try to solve ASAP.

## Installation

Issue following command in console:

```php
composer require jahidhasan3323/easy-crud
```

Alternatively edit composer.json by adding following line and run composer update

```php
"require": {
		....,
		"jahidhasan3323/easy-crud":"^alpha",

	},

```
## Dependency

#### Laravel >= 8.0

## Usage

Run command 
#### php artisan make:easy-crud FeatureName --route='' --request --api
1. FeatureName means your `feature name`. Separate different word by camel case. If you want to create files in a sub folder then write in this format ``folderName/FeatureName`` `Api/Test`. 


2. `--route` is optional parameter if you want to write the route in another file (not in web.php), then pass the file name using this parameter like `--route=routes/api.php`.


3. `--request` parameter is also optional. If you want to make custom ``RequestClass`` then just simply pass `--request`. 


4. `--api` parameter is also optional if you make the feature for API using API purpose then simple pass `--api`. It creates controller as api structure.
```php 
php artisan make:easy-crud Category --route='routes/api.php' --request --api 
```

## Support

[Please open an issue on GitHub](https://github.com/jahidhasan3323/easy-crud/issues)


## License

Create CRUD operation for Laravel application under the [MIT license](https://opensource.org/licenses/MIT).
