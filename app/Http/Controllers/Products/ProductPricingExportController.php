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

        /** @var Country $gb */
        $gb = Country::query()
            ->where('iso_2_code', 'GB')
            ->first();

        $eu = Country::query()
            ->where('has_pricing', 1)
            ->orderBy('iso_2_code')
            ->whereIn('iso_2_code', [
                'AT', 'BE', 'BG', 'HR', 'CY', 'CZ',
                'DK', 'EE', 'FI', 'FR', 'DE', 'GR',
                'HU', 'IE', 'IT', 'LV', 'LT', 'LU',
                'MT', 'NL', 'PL', 'PT', 'RO', 'SK',
                'SI', 'ES', 'SE'
            ])
            ->get();

        $restOfWorld = Country::query()
            ->where('has_pricing', 1)
            ->whereNotIn('id', $eu->pluck('id'))
            ->get();

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
        ];

        foreach ($eu as $country) {
            $code = strtolower($country->iso_2_code);
            $headings[] = 'unit_' . $code . '_fba_price';
            $headings[] = 'unit_' . $code . '_sf_price';
        }


        // add another gap column between end of EU
        // and start of ROW
        $headings[] = '';
        $headings[] = 'row_postage_method';

        foreach ($restOfWorld as $country) {
            $code = strtolower($country->iso_2_code);
            $headings[] = 'unit_' . $code . '_fba_price';
            $headings[] = 'unit_' . $code . '_sf_price';
        }

        $headings[] = '';
        $headings[] = 'ebay_price';
        $headings[] = 'website_price';
        $headings[] = '';
        $headings[] = 'raf_re_order_level';
        $headings[] = 'send_to_uk';
        $headings[] = 'send_to_eu';

        $csv = new \SplTempFileObject(256 * 1024 * 1024);
        $csv->fputcsv($headings);
        /** @var ProductPricing[] $pricings */
        foreach ($pricings as $pricing) {

            $row = $this->createCsvRow(
                $pricing,
                $gb,
                $eu,
                $restOfWorld,
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
                        $row = $this->createCsvRow($pricing, $gb, $eu, $row, $sku, $asin);
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
     * @param Country $gb
     * @param Collection $eu
     * @param Collection $restOfWorld
     * @param $sku
     * @param $asin
     * @return array
     */
    protected function createCsvRow(
        ProductPricing $pricing,
        Country $gb,
        Collection $eu,
        Collection $restOfWorld,
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
        $row['uk_postage_method'] = $pricing->product->uk_postage_method;
        $row = $this->appendProductPricingForCountry($row, $pricing, $gb);
        $row['blank_2'] = '';
        $row['eu_postage_method'] = $pricing->product->eu_postage_method;
        foreach ($eu as $country) {
            $row = $this->appendProductPricingForCountry($row, $pricing, $country);
        }
        $row['blank_3'] = '';
        $row['row_postage_method'] = $pricing->product->row_postage_method;
        foreach ($restOfWorld as $country) {
            $row = $this->appendProductPricingForCountry($row, $pricing, $country);
        }
        $row['blank_4'] = '';

        $price = $this->getProductPricingForCountry($pricing, $gb)->first();
        // ebay price
        if ($price) {
            $row[] = $price->unit_sf_price;
        } else {
            $row[] = 'N/A';
        }
        return $row;
    }


    /**
     * @param ProductPricing $pricing
     * @param Country $country
     * @return ProductPricingCountry|null
     */
    protected function getProductPricingForCountry(ProductPricing $pricing, Country $country)
    {
        return $pricing->product_pricing_countries
            ->where('country_id', $country->id)
            ->first();
    }

    /**
     * @param array $row
     * @param ProductPricing $pricing
     * @param Country $country
     * @return array
     */
    protected function appendProductPricingForCountry($row, $pricing, $country)
    {
        $price = $this->getProductPricingForCountry($pricing, $country);
        if ($price) {
            $row[] = $price->unit_fba_price;
            $row[] = $price->unit_sf_price;
        } else {
            $row[] = 'N/A';
            $row[] = 'N/A';
        }
        return $row;
    }
}
