<?php

Route::group(['middleware' => ['auth:api'], 'as' => 'api.stocktakes.me.', 'prefix' => 'stocktakes/me'], function() {
    Route::resource('stocktakes', 'Api\StockTakes\StockTakesController')->only(['show']);
});
