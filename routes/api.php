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
    Route::resource('settings', 'Api\Settings\SettingsController')->only(['index']);
    Route::resource('products', 'Api\Products\ProductsController')->only(['index']);
    Route::resource('admin/import-mappings', 'Api\ImportMappingsController')->only(['index', 'store']);
    Route::resource('admin/shipping/simple-calculator', 'Api\Calculator\SimpleShippingCalculator')->only(['store']);
});

