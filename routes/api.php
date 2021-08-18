<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['as' => 'api.'], function() {
    Route::post('login', 'Api\AuthController@login');
    Route::resource('suppliers', 'Api\SuppliersController')->only(['index']);
    Route::resource('settings', 'Api\Settings\SettingsController')->only(['index']);
    Route::resource('products.comments', 'Api\Products\CommentsController')->only(['store']);
    Route::resource('products', 'Api\Products\ProductsController')->only(['index', 'show', 'store', 'update', 'delete']);
    Route::resource('currencies', 'Api\Currencies\CurrencyController')->only(['index']);
    Route::resource('sales-orders', 'Api\SalesOrders\SalesOrdersController')->only(['index', 'show', 'store', 'update', 'delete']);
    Route::resource('products.media', 'Api\Products\MediaController')->only(['destroy', 'store']);
    Route::resource('admin/import-mappings', 'Api\ImportMappingsController')->only(['index', 'store']);
    Route::resource('admin/shipping/simple-calculator', 'Api\Calculator\SimpleShippingCalculator')->only(['store']);
});

