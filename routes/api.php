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

    Route::group(['middleware' => ['auth:api', 'role_or_permission:admin|super|products']], function() {
        Route::resource('currencies', 'Api\Currencies\CurrencyController')->only(['store']);
        Route::resource('products.skus', 'Api\Products\SkusController')->only(['store']);
        Route::resource('products.media', 'Api\Products\MediaController')->only(['destroy', 'store']);
        Route::resource('products', 'Api\Products\ProductsController')->only(['index', 'show', 'store', 'update', 'delete']);
        Route::resource('products.history', 'Api\Products\ProductHistoryController')->only(['index']);
        Route::resource('products.comments', 'Api\Products\CommentsController')->only(['store']);
        Route::resource('suppliers', 'Api\SuppliersController')->only(['index']);
        Route::resource('products.landed-costs', 'Api\Products\LandedCostsController')->only(['index']);
    });

    Route::group(['middleware' => ['auth:api', 'role_or_permission:pricing']], function() {
        Route::resource('products.pricing','Api\Pricing\CreatePricingFromProductController')->only(['store']);
    });

    Route::group(['middleware' => ['auth:api', 'role:admin|super']], function() {
        Route::resource('settings', 'Api\Settings\SettingsController')->only(['index']);
        Route::resource('guidance', 'Api\Admin\AmazonGuidanceController')->only(['store']);
        Route::resource('products.landed-costs', 'Api\Products\LandedCostsController')->only(['store']);
        Route::resource('currencies', 'Api\Currencies\CurrencyController')->only(['update']);
        Route::resource('countries', 'Api\Countries\CountriesController')->only(['index', 'update']);
        Route::resource('sales-orders/lines/pricing', 'Api\Pricing\CreatePricingFromOrderLinesController')->only(['store']);
        Route::resource('sales-orders.lines.media', 'Api\SalesOrders\Lines\MediaController')->only(['store']);
        Route::resource('sales-orders', 'Api\SalesOrders\SalesOrdersController')->only(['index', 'show', 'store', 'update', 'destroy']);
        Route::post('sales-orders/restore', 'Api\Admin\RestoreSalesOrderController@restore');
        Route::resource('sales-orders.history', 'Api\SalesOrders\HistoryController')->only(['index']);
        Route::resource('sales-orders.lines', 'Api\SalesOrders\LinesController')->only(['store', 'update', 'destroy']);
        Route::resource('sales-orders.tokens', 'Api\SalesOrders\AccessTokensController')->only(['index', 'store', 'destroy']);
        Route::resource('sales-orders/lines/shipments', 'Api\Shipments\CreateShipmentFromOrderLinesController')->only(['store', 'index']);
        Route::get('sales-orders/{sales_order}/consignments/report', 'Api\SalesOrders\ConsignmentsReportController@index');
        Route::put('sales-orders/lines/{line}/consignments/{consignment}', 'Api\SalesOrders\ConsignmentsReportController@update');
        Route::resource('shipments', 'Api\Shipments\ShipmentsController')->only(['index', 'show', 'update']);
        Route::resource('shipments/import', 'Api\Shipments\ImportController')->only(['store']);
        Route::resource('shipments.media', 'Api\Shipments\MediaController')->only(['store', 'destroy']);
        Route::resource('admin/import-mappings', 'Api\ImportMappingsController')->only(['index', 'store']);
        Route::resource('admin/shipping/simple-calculator', 'Api\Calculator\SimpleShippingCalculator')->only(['store']);
        Route::resource('inspections', 'Api\InspectionReports\InspectionReportsController')->only(['show', 'update', 'index']);
        Route::resource('/admin/import/unit-dimensions', 'Api\Admin\Imports\UnitDimensionsImportController')->only(['store']);
        Route::resource('/admin/import/product-asin', 'Api\Admin\Imports\ProductASINImportController')->only(['store']);
        Route::resource('shipments.landed-costs', 'Api\Shipments\CalculateLandedCostController')->only(['store']);
    });

    Route::group(['middleware' => ['auth:api', 'role:super']], function() {
        Route::resource('shipments', 'Api\Shipments\ShipmentsController')->only(['destroy']);
        Route::resource('admin/suppliers', 'Api\Admin\SuppliersController')->only(['index', 'update', 'store', 'show']);
        Route::resource('admin/suppliers.files', 'Api\Admin\Suppliers\MediaController')->only(['index', 'store']);
        Route::resource('admin/factories', 'Api\Admin\FactoriesController')->only(['update', 'store', 'show']);
        Route::resource('admin/factories.files', 'Api\Admin\Factories\MediaController')->only(['index', 'store']);
        Route::resource('admin/settings', 'Api\Admin\SettingsController')->only(['index', 'store']);
        Route::resource('admin/users', 'Api\Admin\UsersController')->only(['index', 'update', 'store', 'show']);
        Route::resource('admin/documents','Api\Admin\DocumentsController')->only(['index']);
        Route::resource('admin/roles', 'Api\Admin\RolesController')->only(['index', 'store', 'destroy']);
        Route::put('admin/roles', 'Api\Admin\RolesController@updateAll')->name('roles.update');
    });

    Route::group(['as' => 'api.', 'middleware' => ['auth:api', 'role_or_permission:super|pricing|pricing_approve']], function() {
        Route::resource('guidance', 'Api\Pricing\AmazonGuidanceController')->only(['index']);
        Route::resource('pricing','Api\Products\ProductPricingController')->only(['index','update','show']);
        Route::resource('pricing.history', 'Api\Products\ProductPricingHistoryController')->only(['index']);
    });

    Route::group(['middleware' => ['auth:api', 'role:supplier|admin|super']], function() {
        Route::resource('sales-orders.consignments', 'Api\SalesOrders\ConsignmentsController')->only(['index', 'store', 'update']);
    });


    Route::group(['middleware' => ['auth:api','permission:uk']], function () {
        Route::resource('order-acknowledgements', 'Api\Uk\OrderAcknowledgementsController')->only(['index', 'store', 'show', 'destroy', 'update']);
        Route::put('/order-acknowledgements/{order_acknowledgement}/restore', 'Api\Uk\OrderAcknowledgementsController@restore');
        Route::resource('order-acknowledgements.lines', 'Api\Uk\OrderAcknowledgementLinesController')->only('store', 'update', 'destroy');
        Route::resource('order-acknowledgements.lines.product', 'Api\Uk\OrderAcknowledgementProductController')->only('store');
    });


    Route::group(['middleware' => ['auth:api']], function() {
        Route::resource('admin/factories', 'Api\Admin\FactoriesController')->only(['index']);
    });

    Route::group(['middleware' => ['auth:api', 'role:supplier']], function() {
        Route::resource('supplier/sales-orders', 'Api\SalesOrders\SalesOrdersController')->only(['index', 'show', 'store', 'update', 'destroy']);
        Route::resource('supplier/sales-orders.lines', 'Api\SalesOrders\LinesController')->only(['update', 'store', 'destroy']);
    });
});




