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

Route::get('/supplier/tokens/order/{token}', 'Tokens\SalesOrderLoginController@auth')->name('sales-order-token-login');

Route::group(['middleware' => ['auth', 'role:supplier']], function() {
    Route::resource(
        'supplier/sales-orders',
        'SalesOrders\SalesOrdersController',
        ['as' => 'supplier']
    )->only(['edit', 'index', 'store', 'update']);
});

Route::group(['middleware' => ['auth']], function() {
    Route::get('/home', 'HomeController@index')->name('home');
    Route::resource('products', 'Products\ProductsController')->only(['index', 'create', 'edit']);
    Route::resource('sales-orders', 'SalesOrders\SalesOrdersController')->only(['index', 'edit']);
});

Route::group(['middleware' => ['auth', 'role:admin|super']], function() {
    Route::resource('/global-notes', 'Admin\NotesController')->only(['store']);
    Route::resource('/admin/media', 'Admin\MediaController')->only(['show', 'destroy']);
    Route::resource('/admin/imports/preview', 'Admin\Imports\PreviewController')->only(['store', 'update']);
    Route::resource('/admin/imports', 'Admin\Imports\RunController')->only(['create', 'store']);
    Route::resource('sales-orders.export', 'SalesOrders\ExportController')->only(['index']);
    Route::resource('sales-orders.consignments', 'SalesOrders\ConsignmentsController')->only(['index']);
    Route::resource('/inspections', 'InspectionReports\InspectionReportsController')->only(['edit', 'index']);
    Route::resource('/shipments', 'Shipments\ShipmentsController')->only(['index', 'show']);
});

Route::group(['middleware' => ['auth', 'role:super']], function() {
    Route::resource('/admin/currencies', 'Admin\Currencies\CurrencyController')->only(['index', 'create']);
    Route::resource('/admin/countries', 'Admin\Countries\CountriesController')->only(['index']);
    Route::resource('/admin/users', 'Admin\UsersController')->only(['index', 'edit', 'create']);
    Route::resource('/admin/suppliers', 'Admin\SuppliersController')->only(['index', 'edit', 'create']);
    Route::resource('/admin/factories', 'Admin\FactoriesController')->only(['index', 'edit', 'create']);
    Route::resource('/admin/settings', 'Admin\SettingsController')->only(['index']);
    Route::resource('/admin/export-templates', 'Admin\ExportTemplatesController', ['as' => 'settings'])->only(['index', 'store']);
    Route::get('/admin/export-templates/delete', 'Admin\ExportTemplatesController@destroy')->name('settings.export-templates.destroy');
});

Route::group(['middleware' => ['auth', 'role:super|pricing|pricing_approver']], function() {
    Route::resource('price-list', 'Products\ProductPricingController')->only(['index']);
    Route::resource('price-list/export', 'Products\ProductPricingExportController', ['as' => 'price-list'])->only(['index']);
});
