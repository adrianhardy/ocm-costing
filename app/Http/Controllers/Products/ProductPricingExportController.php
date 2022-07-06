<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Currency;
use App\Models\ProductPricing;
use App\Models\ProductPricingCountry;
use Illuminate\Http\Request;

class ProductPricingExportController extends Controller
{
    public function index(Request $request)
    {
        $array = explode(',', $request->input('product_pricing_ids'));
        $pricings = ProductPricing::query()->findMany($array);
        $pricings->load('product_pricing_countries');
        $pricings->load('product');

        $gb = Country::query()->where('iso_2_code', 'GB')->first();

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
            'row_postage_method'
        ];

        $countries = Country::query()
            ->where('has_pricing', 1)
            ->get();

        foreach ($countries as $country) {
            $code = strtolower($country->iso_2_code);
            $headings[] = 'unit_' . $code . '_fba_price';
        }
        foreach ($countries as $country) {
            $code = strtolower($country->iso_2_code);
            $headings[] = 'unit_' . $code . '_sf_price';
        }

        $headings[] = 'ebay_price';
        $headings[] = 'website_price';
        $headings[] = 'raf_re_order_level';
        $headings[] = 'send_to_uk';
        $headings[] = 'send_to_eu';

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

            foreach ($countries as $country) {
                /** @var ProductPricingCountry $price */
                $price = $pricing->product_pricing_countries
                    ->where('country_id', $country->id)
                    ->first();

                $row[] = $price->unit_fba_price;
            }
            foreach ($countries as $country) {
                /** @var ProductPricingCountry $price */
                $price = $pricing->product_pricing_countries
                    ->where('country_id', $country->id)
                    ->first();

                $row[] = $price->unit_sf_price;
            }

            /** @var ProductPricingCountry $price */
            $price = $pricing->product_pricing_countries
                ->where('country_id', $gb->id)
                ->first();

            // ebay price
            $row[] = $price->unit_sf_price;

            $csv->fputcsv($row->toArray());
        }

        $size = $csv->ftell();
        $csv->rewind();

        return response()->streamDownload(function() use ($csv, $size) {
            echo $csv->fread($size);
        }, 'product-pricing.csv');
    }
}
