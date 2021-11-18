<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\ProductPricing;
use App\Models\Repositories\ProductPricingRepository;
use App\Transformers\ProductPricingTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProductPricingController extends Controller
{
    public function index(
        Request $request,
        ProductPricingRepository $productPricingRepository
    ) {

        $paginator = $productPricingRepository;
        if ($request->query->getBoolean('todo_only', false)) {
            // Pricing's to be approved/done:
            $paginator = $paginator
                ->whereNull('superseded_at')
                ->whereNull('approved_at')
                ->paginate($request->input('per_page', 100));
        } else {
            $paginator = $paginator
                ->whereNull('superseded_at')
                ->paginate($request->input('per_page', 100));
        }


        return response()->json(new SerializeLengthAwarePaginator(
            $paginator, new ProductPricingTransformer()
        ));
    }

    public function show(ProductPricing $pricing){
        return response()->json(fractal($pricing, new ProductPricingTransformer()));

    }

    public function update(Request $request, ProductPricing $pricing)
    {
        $this->validate($request, [
            'unit_price_gbp' => 'required|numeric',
            'marketplace_commission_pc' => 'required|numeric',
            'unit_shipping_gbp' => 'required|numeric',
            'unit_sf_uk_price' => 'numeric|min:0|max:99999',
            'unit_sf_fr_price' => 'numeric|min:0|max:99999',
            'unit_sf_de_price' => 'numeric|min:0|max:99999',
            'unit_sf_it_price' => 'numeric|min:0|max:99999',
            'unit_sf_nl_price' => 'numeric|min:0|max:99999',
            'unit_sf_es_price' => 'numeric|min:0|max:99999',
            'unit_sf_se_price' => 'numeric|min:0|max:99999',
        ],[

        ],[
            'unit_sf_uk_price' => 'UK seller-fulfilled price',
            'unit_sf_fr_price' => 'FR seller-fulfilled price',
            'unit_sf_de_price' => 'DE seller-fulfilled price',
            'unit_sf_it_price' => 'IT seller-fulfilled price',
            'unit_sf_nl_price' => 'NL seller-fulfilled price',
            'unit_sf_es_price' => 'ES seller-fulfilled price',
            'unit_sf_se_price' => 'SE seller-fulfilled price'
        ]);

        $pricing->fill($request->only([
            'unit_price_gbp',
            'marketplace_fee',
            'marketplace_commission_pc',
            'unit_shipping_gbp',
            'uk_vat',
            'fr_vat',
            'de_vat',
            'it_vat',
            'nl_vat',
            'es_vat',
            'au_vat',
            'se_vat',
            'unit_fr_price',
            'unit_de_price',
            'unit_it_price',
            'unit_nl_price',
            'unit_es_price',
            'unit_se_price',
            'unit_sf_uk_price',
            'unit_sf_fr_price',
            'unit_sf_de_price',
            'unit_sf_it_price',
            'unit_sf_nl_price',
            'unit_sf_es_price',
            'unit_sf_se_price',
            'unit_fr_shipping',
            'unit_de_shipping',
            'unit_it_shipping',
            'unit_nl_shipping',
            'unit_es_shipping',
            'unit_se_shipping',
            'unit_usd_shipping',
            'unit_cad_shipping',
            'unit_mxn_shipping',
            'unit_aud_shipping',
            'unit_sf_usd_price',
            'unit_sf_cad_price',
            'unit_sf_mxn_price',
            'unit_sf_aud_price',
            'unit_usd_price',
            'unit_cad_price',
            'unit_mxn_price',
            'unit_aud_price',
            'eur_fx_rate',
            'usd_fx_rate',
            'cad_fx_rate',
            'mxn_fx_rate',
            'aud_fx_rate'
        ]));

        if($request->input(['submitted'])){
            $pricing->approved_at = Carbon::now();
            $pricing->approver()->associate(\Auth::user());
        }

        $pricing->save();

        return fractal($pricing->fresh(), new ProductPricingTransformer());
    }
}
