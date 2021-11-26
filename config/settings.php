<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Default Settings Store
	|--------------------------------------------------------------------------
	|
	| This option controls the default settings store that gets used while
	| using this settings library.
	|
	| Supported: "json", "database"
	|
	*/
	'store' => 'database',

	/*
	|--------------------------------------------------------------------------
	| JSON Store
	|--------------------------------------------------------------------------
	|
	| If the store is set to "json", settings are stored in the defined
	| file path in JSON format. Use full path to file.
	|
	*/
	'path' => storage_path().'/settings.json',

	/*
	|--------------------------------------------------------------------------
	| Database Store
	|--------------------------------------------------------------------------
	|
	| The settings are stored in the defined file path in JSON format.
	| Use full path to JSON file.
	|
	*/
	// If set to null, the default connection will be used.
	'connection' => null,
	'table' => 'settings',
	'keyColumn' => 'key',
	'valueColumn' => 'value',

    'defaults' => [
        'cubic_metres_to_feet' => 35.3147,
        'shipping_cost_per_cubic_foot' => 6.50,
        'default_commission_rate_pc' => 4.0,
        'default_import_duty_pc' => 4.7,
        'default_marketplace_commission_pc' => 15.0
    ]
];
