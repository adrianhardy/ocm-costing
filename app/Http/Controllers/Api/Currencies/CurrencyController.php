<?php

namespace App\Http\Controllers\Api\Currencies;

use App\Events\ShippingCostSettingChanged;
use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\Currency;
use App\Transformers\CurrencyTransformer;
use App\Transformers\FactoryTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\PaginatedResourceResponse;

class CurrencyController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $paginator = Currency::query()->orderBy('name')->paginate($request->input('per_page', 15));
        return response()->json(new SerializeLengthAwarePaginator(
            $paginator, new CurrencyTransformer()
        ));
    }

    public function update(Request $request, Currency $currency)
    {
        $currency->setCurrencyFxRateAttribute($request->input('currency_fx_rate'));
        $currency->save();
        event(new ShippingCostSettingChanged());
        return response()->json();
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'code' => 'required|max:3|unique:currencies,code',
            'fx' => 'required|numeric|min:0'
        ]);

        $currency = new Currency($request->only([
            'name',
            'code'
        ]));

        $currency->default_fx_to_gbp = 1/$request->input('fx');
        $currency->save();

        return response()->json(fractal($currency, new CurrencyTransformer()));
    }
}
