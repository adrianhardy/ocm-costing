<?php

namespace App\Http\Controllers\Api\Uk\Pricing;

use App\Domain\Export\DailyPricingExport;
use App\Domain\ProductPricingViabilityChecker;
use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\Country;
use App\Models\DailyPricing;
use App\Models\Product;
use App\Models\ProductPricingCountry;
use App\Models\Repositories\DailyPricingRepository;
use App\Transformers\DailyPricingTransformer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DailyPricingController extends Controller
{
    public function index(Request $request, DailyPricingRepository $repository)
    {
        $paginator = $repository->with(['product', 'country', 'approver', 'country.currency']);

        if($request->input('country_id')) {
            $paginator = $paginator->where('daily_pricing.country_id', $request->input('country_id'));
        }

        if ($request->input('non_viable')) {
            $paginator = $paginator->where('is_viable', 0)
                ->whereNotNull('submitter_id');

            // work around a bug where URLSearchParams will quote 'null' as a string...
            $nvSince = $request->input('non_viable_submitted_since');
            if ($nvSince === 'null') {
                $nvSince = null;
            }

            if ($nvSince) {
                $paginator->where('submitted_at', '>', Carbon::createFromFormat('Y-m-d', $nvSince));
            }
        }

        if($request->input('product_id')) {
            $paginator = $paginator
                ->where('daily_pricing.product_id', $request->input('product_id'));

            // by default, only return approved items
            if ($request->input('include_full_history')) {
                $paginator = $paginator->whereNotNull('submitter_id');
            } else {
                $paginator = $paginator->whereNotNull('approved_at');
            }

        } else {
            $paginator = $paginator->select('daily_pricing.*');
            /*
                SELECT MAX(id) as id, product_id, country_id, MAX(submitted_at) as submitted_at, GROUP_CONCAT(id), GROUP_CONCAT(submitted_at)
                FROM daily_pricing
                WHERE submitted_at IS NOT NULL
                GROUP BY country_id, product_id,
                ORDER BY MAX(submitted_at) DESC
             */
            $subQuery = DB::table('daily_pricing AS dp')
                ->addSelect(DB::raw('MAX(dp.id) as id'))
                ->addSelect('dp.product_id')
                ->addSelect('dp.country_id')
                // for debugging:
                // ->addSelect(DB::raw('MAX(submitted_at) as submitted_at'))
                // ->addSelect(DB::raw('GROUP_CONCAT(id)'))
                // ->addSelect(DB::raw('GROUP_CONCAT(submitted_at)'))
                ->whereNotNull('dp.submitted_at')
                ->groupBy('dp.country_id')
                ->groupBy('dp.product_id')
                ->orderByRaw('MAX(submitted_at) DESC');

            $paginator = $paginator->joinSub($subQuery, 'latest_daily_pricing', 'latest_daily_pricing.id', '=', 'daily_pricing.id');
        }


        switch ($request->input('orderBy')) {
            case 'products.item_number':
            case 'products.name':
            case 'products.primary_sku':
            case 'products.primary_asin':
                $paginator = $paginator
                    ->leftJoin('products', 'products.id', '=', 'daily_pricing.product_id');
                break;
        }

        // SINCE 23/05/2022 the below reasoning is incorrect:
        // We only put this in because we needed the "unit_fba_shipping"
        // amount to accurately work out the cost, and thus viability. However,
        // Cat says that they'll only do a full round of pricing when they get
        // an invoice from a supplier, so we can't insist on there being product
        // pricings available...

        // OLD AND WRONG ASSUMPTION:
        // there must be at least one 'product_pricings' associated with this
        // daily pricing before we can allow it to show...
        // $paginator = $paginator->has('product_pricings');

        if($request->input('export')) {
            set_time_limit(120);
            ini_set('memory_limit', '256M');
            $runExport = new DailyPricingExport([]);
            $paginator->chunk(500, function ($pricings) use ($runExport) {
                $runExport->setPricings($pricings);
                $runExport->buildExport();
            });
            $export = $runExport->getXLSDownloadResponse();
            $export->downloadAs('DailyPricingExport.xls');
            die();
        }

        $paginator = $paginator->paginate($request->input('per_page', 100));


        return response()->json(new SerializeLengthAwarePaginator($paginator, new DailyPricingTransformer()));
    }

    /**
     * User submits a new price for a given product/country combo ....
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'product_id'            => 'required|exists:products,id',
            'country_id'            => 'required|exists:countries,id',
            'suggested_fba_fee'     => 'required|numeric|nullable|min:0',
            'suggested_price'       => 'required|numeric|nullable|min:0'
        ]);



        $pricing = new DailyPricing();
        $pricing->country()->associate(Country::query()->findOrFail($request->input('country_id')));
        $pricing->product()->associate(Product::query()->findOrFail($request->input('product_id')));

        try{
            $pricing->product->current_product_pricing;
        } catch (\Exception $ex) {
            abort(400, 'This product has not been pushed through to pricing yet');
        }


        try {
            $pricing->suggested_price = $request->input('suggested_price');
            $pricing->fba_fee = $request->input('suggested_fba_fee');
            $pricing->submitted_at = Carbon::now();
            $pricing->submitter()->associate(auth()->user());
            $pricing->save();
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }

        return response()->json(fractal($pricing, new DailyPricingTransformer()));
    }


    /**
     * @param Request $request
     * @param DailyPricing $dailyPricing
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function show(Request $request, DailyPricing $dailyPricing)
    {
        $this->validate($request, [
            'suggested_fba_fee'     => 'required|numeric|nullable|min:0',
            'suggested_price'       => 'required|numeric|nullable|min:0'
        ]);

        // NOTE: we're not planning on SAVING this, we're just updating it
        // so we can recalc margins and test viability, then we explicitly do
        // ***NOT SAVE***
        DB::beginTransaction();
        $dailyPricing->suggested_price = $request->input('suggested_price');
        $dailyPricing->fba_fee = $request->input('suggested_fba_fee');

        $dailyPricing->updateViability();
        DB::rollback();

        return response()->json(fractal($dailyPricing, new DailyPricingTransformer()));
    }
}
