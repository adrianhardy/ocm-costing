<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json([
            'items' => [
                'cubic_metres_to_feet'      => setting('cubic_metres_to_feet'),
                'shipping_cost_per_cubic_foot' => setting('shipping_cost_per_cubic_foot'),
                'default_commission_rate' => setting('default_commission_rate'),
                'default_import_duty' => setting('default_import_duty'),
            ]
        ]);
    }
}
