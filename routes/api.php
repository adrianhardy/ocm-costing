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

    Route::group(['middleware' => ['auth:api', 'role:admin|super|supplier']], function() {
        Route::resource('products.comments', 'Api\Products\CommentsController')->only(['store']);
        Route::resource('products', 'Api\Products\ProductsController')->only(['index', 'show', 'store', 'update', 'delete']);
        Route::resource('products.skus', 'Api\Products\SkusController')->only(['store']);
        Route::resource('products.history', 'Api\Products\ProductHistoryController')->only(['index']);
        Route::resource('products.media', 'Api\Products\MediaController')->only(['destroy', 'store']);
        Route::resource('currencies', 'Api\Currencies\CurrencyController')->only(['index']);
    });

    Route::group(['middleware' => ['auth:api', 'role:admin|super']], function() {
        Route::resource('suppliers', 'Api\SuppliersController')->only(['index']);
        Route::resource('settings', 'Api\Settings\SettingsController')->only(['index']);
        Route::resource('products.landed-costs', 'Api\Products\LandedCostsController')->only(['index', 'store']);
        Route::resource('currencies', 'Api\Currencies\CurrencyController')->only(['update']);
        Route::resource('sales-orders/lines/pricing', 'Api\Pricing\CreatePricingFromOrderLinesController')->only(['store']);
        Route::resource('sales-orders.lines.media', 'Api\SalesOrders\Lines\MediaController')->only(['store']);
        Route::resource('sales-orders', 'Api\SalesOrders\SalesOrdersController')->only(['index', 'show', 'store', 'update', 'delete']);
        Route::resource('sales-orders.landed-costs', 'Api\SalesOrders\CalculateLandedCostController')->only(['store']);
        Route::resource('sales-orders.history', 'Api\SalesOrders\HistoryController')->only(['index']);
        Route::resource('sales-orders.lines', 'Api\SalesOrders\LinesController')->only(['store', 'update', 'destroy']);
        Route::resource('sales-orders.tokens', 'Api\SalesOrders\AccessTokensController')->only(['index', 'store', 'destroy']);
        Route::resource('sales-orders/lines/shipments', 'Api\Shipments\CreateShipmentFromOrderLinesController')->only(['store', 'index']);
        Route::get('sales-orders/{sales_order}/consignments/report', 'Api\SalesOrders\ConsignmentsReportController@index');
        Route::put('sales-orders/lines/{line}/consignments/{consignment}', 'Api\SalesOrders\ConsignmentsReportController@update');
        Route::resource('admin/import-mappings', 'Api\ImportMappingsController')->only(['index', 'store']);
        Route::resource('admin/shipping/simple-calculator', 'Api\Calculator\SimpleShippingCalculator')->only(['store']);
        Route::resource('inspections', 'Api\InspectionReports\InspectionReportsController')->only(['show', 'update', 'index']);
    });

    Route::group(['middleware' => ['auth:api', 'role:super']], function() {
        Route::resource('admin/suppliers', 'Api\Admin\SuppliersController')->only(['index', 'update', 'store', 'show']);
        Route::resource('admin/suppliers.files', 'Api\Admin\Suppliers\MediaController')->only(['index', 'store']);
        Route::resource('admin/factories', 'Api\Admin\FactoriesController')->only(['index', 'update', 'store', 'show']);
        Route::resource('admin/factories.files', 'Api\Admin\Factories\MediaController')->only(['index', 'store']);
        Route::resource('admin/settings', 'Api\Admin\SettingsController')->only(['index', 'store']);
        Route::resource('admin/users', 'Api\Admin\UsersController')->only(['index', 'update', 'store', 'show']);
    });

    Route::group(['as' => 'api.', 'middleware' => ['auth:api', 'role:super|pricing']], function() {
        Route::resource('pricing','Api\Products\ProductPricingController')->only(['index','update','show']);
        Route::resource('pricing.history', 'Api\Products\ProductPricingHistoryController')->only(['index']);
    });

    Route::group(['middleware' => ['auth:api', 'role:supplier|admin|super']], function() {
        Route::resource('sales-orders.consignments', 'Api\SalesOrders\ConsignmentsController')->only(['index', 'store', 'update']);
    });


    Route::group(['middleware' => ['auth:api', 'role:supplier']], function() {
        Route::resource('supplier/sales-orders', 'Api\SalesOrders\SalesOrdersController')->only(['index', 'show', 'store', 'update', 'delete']);
        Route::resource('supplier/sales-orders.lines', 'Api\SalesOrders\LinesController')->only(['update', 'store', 'delete']);
    });
});




