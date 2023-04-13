<?php

namespace App\Http\Controllers\Api\Uk\Pricing;

use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\DailyPricing;
use App\Models\Repositories\DailyPricingRepository;
use App\Services\PricingService;
use App\Transformers\DailyPricingTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DailyPricingController extends Controller
{
    public function index(Request $request, DailyPricingRepository $repository)
    {
        $paginator = $repository;


        if($request->input('country_id')) {
            $paginator = $paginator->where('country_id', $request->input('country_id'));
        }

        $paginator = $paginator->paginate($request->input('per_page', 100));


        return response()->json(new SerializeLengthAwarePaginator($paginator, new DailyPricingTransformer()));
    }

    public function update(Request $request, DailyPricing $dailyPricing, PricingService $pricingService)
    {

        if($request->input('approve')) {
            $dailyPricing->approved_at = Carbon::now();
            $dailyPricing->approver()->associate(auth()->user());

            $dailyPricing->product->unit_price_gbp = $dailyPricing->suggested_price;
            $dailyPricing->product->save();


        } else {
            $this->validate($request, [
                'fba_fee' => 'sometimes|numeric|nullable',
                'suggested_price' => 'sometimes|numeric|nullable'
            ]);

            $dailyPricing->fill($request->only([
                'fba_fee', 'suggested_price'
            ]));
        }

        $dailyPricing->save();

        return response()->json(fractal($dailyPricing, new DailyPricingTransformer()));
    }
}
