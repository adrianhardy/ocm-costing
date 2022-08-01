<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Currency;
use App\Models\ProductPricing;
use App\Models\ProductPricingCountry;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProductPricingExportController extends Controller
{
    public function index(Request $request)
    {
        $array = explode(',', $request->input('product_pricing_ids'));
        $pricings = ProductPricing::query()->findMany($array);
        $pricings->load('product_pricing_countries');
        $pricings->load('product');

        $gb = Country::query()
            ->where('iso_2_code', 'GB')
            ->first();


        $headings = [
            'item_number',
            'name',
            'barcode',
            'SKU',
            'ASIN',
            '',
            'uk_postage_method',
            'unit_gb_sf_price',
            'unit_gb_fba_price',
            '',
            'eu_postage_method',
            'row_postage_method'
        ];

        $countries = Country::query()
            ->where('has_pricing', 1)
            ->where('id', '!=', 'gb')
            ->get();

        $countries = collect([$gb])->concat($countries);

        foreach ($countries as $country) {
            $code = strtolower($country->iso_2_code);
            $headings[] = 'unit_' . $code . '_fba_price';
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

            $row = $this->createCsvRow(
                $pricing,
                $countries,
                $gb,
                $pricing->product->primary_sku,
                $pricing->product->primary_asin
            );

            $csv->fputcsv($row->toArray());

            $asins = $pricing->product->additional_asins;
            $skus = $pricing->product->additional_skus;

            if (is_array($asins) || is_array($skus)) {
                $biggestCount = max(count($asins), count($skus));
                $asins = array_pad($asins, $biggestCount, null);
                $skus = array_pad($skus, $biggestCount, null);

                if ($biggestCount > 0) {
                    foreach ($asins as $i => $asin) {
                        $sku = $skus[$i]??null;
                        $row = $this->createCsvRow($pricing, $countries, $gb, $sku, $asin);
                        $csv->fputcsv($row->toArray());
                    }
                }
            }
        }

        $size = $csv->ftell();
        $csv->rewind();

        return response()->streamDownload(function() use ($csv, $size) {
            echo $csv->fread($size);
        }, 'product-pricing.csv');
    }

    /**
     * @param ProductPricing $pricing
     * @param Collection $countries
     * @param $gb
     * @return Collection
     */
    protected function createCsvRow(
        ProductPricing $pricing,
        Collection $countries,
        $gb,
        $sku = '',
        $asin = ''
    ) {
        $row = collect($pricing->product->only([
            'item_number',
            'name',
            'barcode'
        ]));

        $row['sku'] = $sku;
        $row['asin'] = $asin;
        $row['blank_1'] = '';

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

            if ($price) {
                $row[] = $price->unit_fba_price;
                $row[] = $price->unit_sf_price;
            } else {
                $row[] = 'N/A';
                $row[] = 'N/A';
            }

        }

        /** @var ProductPricingCountry $price */
        $price = $pricing->product_pricing_countries
            ->where('country_id', $gb->id)
            ->first();

        // ebay price
        if ($price) {
            $row[] = $price->unit_sf_price;
        } else {
            $row[] = 'N/A';
        }
        return $row;
    }
}
