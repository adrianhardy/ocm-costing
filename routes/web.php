<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes(['register' => false]);
Route::redirect('/', '/home');
Route::group(['middleware' => ['auth', 'role:super|admin']], function() {
    Route::resource('/admin/media', 'Admin\MediaController')->only(['show', 'destroy']);
    Route::get('/home', 'HomeController@index')->name('home');
    Route::resource('/admin/imports/preview', 'Admin\Imports\PreviewController')->only(['store', 'update']);
    Route::resource('/admin/imports', 'Admin\Imports\RunController')->only(['create', 'store']);
    Route::resource('products', 'Products\ProductsController')->only(['index', 'create', 'edit']);
    Route::resource('sales-orders', 'SalesOrders\SalesOrdersController')->only(['index', 'edit']);
    Route::resource('sales-orders.export', 'SalesOrders\ExportController')->only(['index']);
    Route::resource('sales-orders.consignments', 'SalesOrders\ConsignmentsController')->only(['index']);
});

Route::group(['middleware' => ['auth', 'role:admin|super']], function() {
    Route::resource('/admin/users', 'Admin\UsersController')->only(['index', 'edit', 'create']);
    Route::resource('/admin/suppliers', 'Admin\SuppliersController')->only(['index', 'edit', 'create']);
    Route::resource('/admin/factories', 'Admin\FactoriesController')->only(['index', 'edit', 'create']);
    Route::resource('/admin/settings', 'Admin\SettingsController')->only(['index']);
});

Route::group(['middleware' => ['auth', 'role:super|pricing']], function() {
    Route::resource('price-list', 'Products\ProductPricingController')->only(['index']);

});
