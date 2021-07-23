<?php

Route::group(['middleware' => ['auth:api'], 'as' => 'api.packs.me.', 'prefix' => 'packs/me'], function() {
    Route::resource('/shipments', 'Api\Packing\ShipmentsController')->only(['index', 'show', 'destroy']);
    Route::resource('/lines', 'Api\Packing\LinesController')->only(['update']);
});
