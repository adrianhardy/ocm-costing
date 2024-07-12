<?php

namespace App\Http\Controllers\Api\Currencies;

use App\Events\ShippingCostSettingChanged;
use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\Currency;
use App\Models\DailyPricing;
use App\Models\Product;
use App\Services\PricingService;
use App\Transformers\CurrencyTransformer;
use App\Transformers\FactoryTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\PaginatedResourceResponse;
use Illuminate\Support\Facades\DB;

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

    /**
     * @param Request $request
     * @param Currency $currency
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Currency $currency, PricingService $pricingService)
    {
        $this->validate($request, [
            'currency_fx_rate' => 'required|numeric|min:0',
            'pricing_fx_rate' => 'sometimes|nullable|numeric'
        ]);

        $currency->currency_fx_rate = $request->input('currency_fx_rate');

        // to undo/change the pricing_fx_rate, you need to send in the empty string.
        // if we get null, we'll assume that the person didn't mean to change it.
        if ($request->input('pricing_fx_rate') == '') {
            $currency->pricing_fx_to_gbp = null;
        } elseif ($request->input('pricing_fx_rate')) {
            $currency->pricing_fx_rate = $request->input('pricing_fx_rate');
        }
        $currency->save();

        set_time_limit(60 * 30); // 30 minutes should be enough

        if ($currency->wasChanged(['currency_fx_rate'])) {
            event(new ShippingCostSettingChanged());
        }

        return response()->json(fractal($currency->fresh(), new CurrencyTransformer()));
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
