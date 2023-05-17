<?php

namespace App\Http\Controllers\Api\Uk\Pricing;

use App\Domain\Export\DailyPricingExport;
use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\DailyPricing;
use App\Models\ProductPricingCountry;
use App\Models\Repositories\DailyPricingRepository;
use App\Transformers\DailyPricingTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyPricingController extends Controller
{
    public function index(Request $request, DailyPricingRepository $repository)
    {
        $paginator = $repository->with(['product', 'country', 'approver']);

        if($request->input('country_id')) {
            $paginator = $paginator->where('country_id', $request->input('country_id'));
        }

        if($request->input('product_id')) {
            $paginator = $paginator
                ->where('product_id', $request->input('product_id'))
                ->whereNotNull('approved_at');
        }


        if($request->input('export')) {
            $paginator = $paginator->where('is_viable', false);

            if (Auth::check()) {
                abort_unless($request->user()->hasPermissionTo('daily_pricing_export'),
                403,
                'You do not have permission to export');
            }
            set_time_limit(120);
            ini_set('memory_limit', '256M');
            $export = new DailyPricingExport([]);
            $paginator->chunk(500, function ($pricings) use ($export) {
                $export->setPricings($pricings);
                $export->buildExport();
            });
            $export->setPricings([]);
            return $export->getCsvDownloadResponse();
        }

        $paginator = $paginator->paginate($request->input('per_page', 100));


        return response()->json(new SerializeLengthAwarePaginator($paginator, new DailyPricingTransformer()));
    }

    public function update(Request $request, DailyPricing $dailyPricing)
    {

        if($request->input('approve')) {
            $dailyPricing->approved_at = Carbon::now();
            $dailyPricing->approver()->associate(auth()->user());


            $factor = $dailyPricing->country->currency->currency_fx_rate;
            // PRICING is entered in local currency, no need for conversion
            $factor = 1; // undo currency conversion below


            $dailyPricing->product->current_product_pricing->save();

            /** @var ProductPricingCountry $pricingCountry */
            $pricingCountry = $dailyPricing->product->current_product_pricing->product_pricing_countries()->where('country_id', $dailyPricing->country_id)->firstOrFail();
            $pricingCountry->unit_fba_price = ($factor * $dailyPricing->suggested_price);
            $pricingCountry->unit_fba_fee = $dailyPricing->fba_fee;
            $pricingCountry->save();

            $newDailyPricing = new DailyPricing();
            $newDailyPricing->country()->associate($dailyPricing->country);
            $newDailyPricing->product()->associate($dailyPricing->product);
            $newDailyPricing->submitted_at = Carbon::now();
            $newDailyPricing->submitter()->associate(auth()->user());
            $newDailyPricing->save();

        } else {
            $this->validate($request, [
                'fba_fee' => 'sometimes|numeric|nullable|min:0',
                'suggested_price' => 'sometimes|numeric|nullable|min:0'
            ]);

            $dailyPricing->fill($request->only([
                'fba_fee', 'suggested_price'
            ]));

            $dailyPricing->submitter()->associate(auth()->user());

            $dailyPricing->submitted_at = Carbon::now();
            $dailyPricing->submitter()->associate(auth()->user());
        }

        $dailyPricing->save();

        return response()->json(fractal($dailyPricing, new DailyPricingTransformer()));
    }
}
