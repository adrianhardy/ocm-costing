<?php


Route::group(['middleware' => ['auth:api'], 'as' => 'api.checks.me.', 'prefix' => 'checks/me'], function() {
    Route::resource('/shipments', 'Api\Checking\ShipmentsController')->only(['index', 'show', 'destroy']);
    Route::resource('/lines', 'Api\Checking\LinesController')->only(['update']);
});
