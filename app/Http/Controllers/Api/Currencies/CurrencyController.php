<?php

namespace App\Http\Controllers\Api\Currencies;

use App\Events\ShippingCostSettingChanged;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Transformers\CurrencyTransformer;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return response()->json([
            'items' => fractal(Currency::query()->orderBy('name')->get(), new CurrencyTransformer)
        ]);
    }

    public function update(Request $request, Currency $currency)
    {
        $currency->setCurrencyFxRateAttribute($request->input('currency_fx_rate'));
        $currency->save();
        event(new ShippingCostSettingChanged());
        return response()->json();
    }
}
