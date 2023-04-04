<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\Country;
use App\Models\Currency;
use App\Models\ProductPricing;
use App\Models\Repositories\ProductPricingRepository;
use App\Transformers\ProductPricingTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductPricingController extends Controller
{
    public function index(
        Request $request,
        ProductPricingRepository $productPricingRepository
    ) {

        $with = ['product'];

        $paginator = $productPricingRepository;


        $paginator = $paginator->whereNull('superseded_at');


        if ($request->query->getBoolean('todo_only', false)) {
            // Pricing's to be approved/done:

            if(auth()->user()->hasPermissionTo('pricing_approve')) {
                $paginator = $paginator
                    ->whereNull('approved_at')
                    ->whereNotNull('submitted_at');
            } elseif (auth()->user()->hasPermissionTo('pricing')) {
                $paginator = $paginator
                    ->whereNull('submitted_at')
                    ->whereNull('approved_at');
            }
        }

        $paginator = $paginator->paginate($request->input('per_page', 100));

        $paginator->load($with);
        return response()->json(new SerializeLengthAwarePaginator(
            $paginator, new ProductPricingTransformer()
        ));
    }

    public function show(ProductPricing $pricing)
    {
        $transformer = new ProductPricingTransformer();
        return response()->json(fractal(
            $pricing,
            $transformer
        ));
    }

    /**
     * @param Request $request
     * @param ProductPricing $pricing
     * @return \Spatie\Fractal\Fractal
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, ProductPricing $pricing)
    {
        $this->validate($request, [
            'marketplace_commission_pc'     => 'required|numeric|min:0',
            'current_eur_fx_rate'           => 'required|numeric|min:0',
            'est_landed_cost_per_unit_gbp'  => 'required|numeric|min:0',
            'countries.*.current_gbp_fx_rate' => 'sometimes|nullable|numeric|min:0',
            'countries.*.unit_fba_fee'      => 'sometimes|nullable|numeric|min:0',
            'countries.*.unit_fba_price'    => 'sometimes|nullable|numeric|min:0',
            'countries.*.unit_sf_price'     => 'sometimes|nullable|numeric|min:0',
            'countries.*.unit_sf_shipping'  => 'sometimes|nullable|numeric|min:0',
            'countries.*.unit_fba_shipping' => 'sometimes|nullable|numeric|min:0',
            'countries.*.unit_vat'          => 'sometimes|nullable|numeric|min:0',
            'countries.*.vat_rate'          => 'sometimes|nullable|numeric|min:0',
        ],[
            'est_landed_cost_per_unit_gbp' => 'Estimated Landed Cost',
            'current_eur_fx_rate' => 'Euros to GBP exchange rate',
            'marketplace_commission_pc' => 'Marketplace commission %'
        ],[
            'countries.*.unit_fba_fee' => 'FBA fee',
            'countries.*.unit_fba_price' => 'FBA price',
            'countries.*.unit_sf_price' => 'seller-fulfilled price',
            'countries.*.vat_rate' => 'VAT Rate %',
            'countries.*.unit_vat' => 'Unit VAT',
        ]);

        DB::beginTransaction();
        $pricing->fill($request->only([
            'current_eur_fx_rate',
            'marketplace_commission_pc',
            'est_landed_cost_per_unit_gbp'
        ]));
        // copy the global "EUR" rate, set on ProductPricing, down to
        // all the ProductPricingCountry objects related to this $pricing
        foreach ($pricing->product_pricing_countries as $price) {
            if ($price->country->currency->code == 'EUR') {
                $price->gbp_fx_rate = $pricing->eur_fx_rate;
            }
            $data = $request->input(
                'countries.' . strtolower($price->country->iso_2_code)
            );
            $price->fill(collect($data)->only([
                'vat_rate',
                'unit_fba_price',
                'unit_sf_price',
                'unit_fba_fee',
                'unit_fba_shipping'
            ])->toArray());
            $price->save();
        }

        if ($request->input('product.pricing_notes') !== null) {
            $pricing->product->pricing_notes = $request->input('product.pricing_notes');
            $pricing->product->save();
        }

        if($request->input(['submitted'])){
            if(auth()->user()->hasPermissionTo('pricing_approve') || auth()->user()->hasRole('admin')) {
                $pricing->approved_at = Carbon::now();
                $pricing->approver()->associate(auth()->user());
            } else {
                $pricing->submitted_at = Carbon::now();
                $pricing->submitter()->associate(auth()->user());
            }
        }

        $pricing->save();
        DB::commit();
        return fractal($pricing->fresh(), new ProductPricingTransformer());
    }
}
