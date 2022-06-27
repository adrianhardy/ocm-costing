<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\ProductPricing;
use Illuminate\Http\Request;

class ProductPricingExportController extends Controller
{
    public function index(Request $request)
    {
        $array = explode(',', $request->input('product_pricing_ids'));
        $pricings = ProductPricing::query()->findMany($array);
        $pricings->load('product');

        $headings = [
            'item_number',
            'name',
            'barcode',
            'primary_sku',
            'primary_asin',
            'additional_skus',
            'additional_asins',
            'uk_postage_method',
            'eu_postage_method',
            'row_postage_method',
            'unit_fr_price',
            'unit_de_price',
            'unit_it_price',
            'unit_nl_price',
            'unit_es_price',
            'unit_se_price',
            'unit_usd_price',
            'unit_cad_price',
            'unit_mxn_price',
            'unit_aud_price',
            'unit_pln_price',
            'unit_sf_uk_price',
            'unit_sf_fr_price',
            'unit_sf_fr_price',
            'unit_sf_de_price',
            'unit_sf_it_price',
            'unit_sf_nl_price',
            'unit_sf_es_price',
            'unit_sf_au_price',
            'unit_sf_se_price',
            'unit_sf_usd_price',
            'unit_sf_cad_price',
            'unit_sf_mxn_price',
            'unit_sf_aud_price',
            'unit_sf_pln_price',
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
            'unit_pln_shipping',
            'ebay_price',
            'website_price',
            'raf_re_order_level',
            'send_to_uk',
            'send_to_eu',
        ];

        $csv = new \SplTempFileObject(256 * 1024 * 1024);
        $csv->fputcsv($headings);
        /** @var ProductPricing[] $pricings */
        foreach ($pricings as $pricing) {
            $row = collect($pricing->product->only([
                'item_number',
                'name',
                'barcode',
                'primary_sku',
                'primary_asin',
            ]));

            $row['additional_skus'] = implode(',', $pricing->product->additional_skus??[]);
            $row['additional_asins'] = implode(',', $pricing->product->additional_asins??[]);

            $row = $row->concat($pricing->product->only(
                'uk_postage_method',
                'eu_postage_method',
                'row_postage_method'
            ));

            $row = $row->concat($pricing->only([
                'unit_price_gbp',
                'unit_fr_price',
                'unit_de_price',
                'unit_it_price',
                'unit_nl_price',
                'unit_es_price',
                'unit_se_price',
                'unit_usd_price',
                'unit_cad_price',
                'unit_mxn_price',
                'unit_aud_price',
                'unit_pln_price',
                'unit_sf_price',
                'unit_sf_uk_price',
                'unit_sf_fr_price',
                'unit_sf_fr_price',
                'unit_sf_de_price',
                'unit_sf_it_price',
                'unit_sf_nl_price',
                'unit_sf_es_price',
                'unit_sf_au_price',
                'unit_sf_se_price',
                'unit_sf_usd_price',
                'unit_sf_cad_price',
                'unit_sf_mxn_price',
                'unit_sf_aud_price',
                'unit_sf_pln_price',
                'unit_sf_price'
            ]));
            $csv->fputcsv($row->toArray());
        }

        $size = $csv->ftell();
        $csv->rewind();

        return response()->streamDownload(function() use ($csv, $size) {
            echo $csv->fread($size);
        }, 'product-pricing.csv');
    }
}
