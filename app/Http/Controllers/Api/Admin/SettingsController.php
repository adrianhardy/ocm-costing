<?php

namespace App\Http\Controllers\Api\Admin;

use App\Events\ShippingCostPerCubicFootChanged;
use App\Events\ShippingCostSettingChanged;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {

        $defaults = config('settings.defaults');

        $all = collect($defaults)
            ->merge(setting()->all())
            ->except('__history');

        $json = [];
        foreach ($all as $key => $value) {
            $type = 'default';
            $label = $key;
            if (substr($key, -3) == '_pc') {
                $label = substr($key, 0, -3);
                $type = 'percent';
            }
            if (substr($key, -4) == '_usd') {
                $label = substr($key, 0, -4);
                $type = 'usd';
            }
            $json[] = [
                'name' => $key,
                'type' => $type,
                'label' => ucwords(str_replace('_', ' ', $label)),
                'value' => $value
            ];
        }

        return response()->json([
            'items' =>  $json
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'value' => 'required|numeric|min:0|max:10000'
        ]);

        $fieldsToTrack = [
            'shipping_cost_per_cubic_foot',
            'default_commission_rate_pc',
            'default_import_duty_pc',
        ];

        $originalValues = [];
        foreach ($fieldsToTrack as $field) {
            $originalValues[$field] = setting($field);
        }

        $obj = setting('__history', []);
        $time = Carbon::now()->format('Y-m-d H:i:s');
        $obj[$time] = collect(setting()->all())->except('__history');
        setting()->set('__history', $obj);
        setting()->set(
            $request->input('key'),
            $request->input('value')
        );
        setting()->save();


        foreach ($fieldsToTrack as $field) {
            if (setting($field) != $originalValues[$field]) {
                // Force a recalculation of all ELCs
                event(new ShippingCostSettingChanged());
                // Only fire it once if any setting has changed ...
                break;
            }
        }
        return response()->json();
    }
}
