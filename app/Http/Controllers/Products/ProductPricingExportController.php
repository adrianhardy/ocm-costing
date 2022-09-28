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
    // this is used to determine whether the prices
    // for the countries appear in the first section
    // or the second RoW section ... Any countries
    // not in this list will appear in the RoW section
    protected $euIso2Codes = [
        'AT', 'BE', 'BG', 'HR', 'CY', 'CZ',
        'DK', 'EE', 'FI', 'FR', 'DE', 'GR',
        'HU', 'IE', 'IT', 'LV', 'LT', 'LU',
        'MT', 'NL', 'PL', 'PT', 'RO', 'SK',
        'SI', 'ES', 'SE'
    ];

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

        /** @var Collection|Country[] $eu */
        $eu = Country::query()
            ->where('has_pricing', 1)
            ->orderBy('iso_2_code')
            ->whereIn('iso_2_code', $this->euIso2Codes)
            ->get()
            ->sortBy(function(Country $country) {
                $code = strtolower($country->iso_2_code);
                $order = [
                    'de' => '0000',
                    'fr' => '0001',
                    'it' => '0002',
                    'es' => '0003',
                    'nl' => '0004',
                    'se' => '0005',
                    'pl' => '0006',
                ];
                // sort by the order given above, if it's a country
                // that isn't specified, then it'll be sorted
                // alphabetically
                return $order[$code]??('99' . $code);
            });


        $restOfWorld = Country::query()
            ->where('has_pricing', 1)
            ->whereNotIn('id', $eu->pluck('id'))
            ->where('id', '!=', $gb->id)
            ->get()
            ->sortBy(function(Country $country) {
                $code = strtolower($country->iso_2_code);
                $order = [
                    'us' => '0000',
                    'ca' => '0001',
                    'mx' => '0002',
                    'au' => '0003',
                ];
                return $order[$code]??('99' . $code);
            });

        $headings = [
            'SKU',
            'ITEM NUMBER',
            'DESCRIPTION',
            'WEIGHT',
            'DIMENSIONS',
            'ASIN',
            'QTY DELIVERED',
            'QTY TO LIST',
            'UK',
            'PP',
            'FBA',
            'TL/PIGGY',
            'VARIATION',
            'CHECKED LISTING 1',
            'CHECKED LISTING 2',
            'PHOTO',
            'SEND UK',
            'SEND EU',
            'RAF REORDER LEVEL',
            'WEBSITE',
            'WEB QTY',
            'EBAY',
            'EBAY QTY',
            'ADDED SS',
            'PAUL',
            'TAGS',
            'CLICK AND DROP',
        ];

        foreach ($eu as $country) {
            // PL doesn't have SF or PP columns
            if ($country->iso_2_code == 'PL') {
                $headings[] = 'POLAND FBA';
            } else {
                $headings[] = strtoupper($country->name);
                $headings[] = 'PP';
                $headings[] = 'FBA';
            }
        }
        // Row countries only have self fulfilled and PP cols
        // FBA is not used for ROW
        foreach ($restOfWorld as $country) {
            $headings[] = strtoupper($country->name);
            $headings[] = 'PP';
        }

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

            $csv->fputcsv($row);

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
     *
     *
     * @param ProductPricing $pricing
     * @param Country $gb
     * @param Collection $eu
     * @param Collection $restOfWorld
     * @param string $sku
     * @param string $asin
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
        $row[] = $sku;
        $row[] = $pricing->product->item_number;
        $row[] = $pricing->product->name;
        $row[] = $pricing->product->unit_weight_g . 'G';
        $row[] = "{$pricing->product->unit_length_cm} x {$pricing->product->unit_width_cm} x {$pricing->product->unit_height_cm}";
        $row[] = $asin;
        $row[] = ''; // qty delivered
        $row[] = ''; // qty to list
        $row = $this->appendProductPricingForCountry($row, $pricing, $gb, false);
        $row[] = ''; // TL/PIGGY
        $row[] = ''; // VARIATION
        $row[] = ''; // CHECKED LIST 1
        $row[] = ''; // CHECKED LIST 2
        $row[] = ''; // PHOTO
        $row[] = ''; // SEND UK
        $row[] = ''; // SEND EU
        $row[] = ''; // RAF REORDER LEVEL
        $row[] = ''; // WEBSITE
        $row[] = ''; // WEB QTY

        $ukPrice = $this->getProductPricingForCountry($pricing, $gb);
        $row[] = $ukPrice->unit_sf_price; // EBAY
        $row[] = ''; // EBAY QTY
        $row[] = ''; // ADDED SS
        $row[] = ''; // PAUL
        $row[] = ''; // TAGS
        $row[] = ''; // CLICK AND DROP
        foreach ($eu as $country) {
            $row = $this->appendProductPricingForCountry($row, $pricing, $country, false);
        }
        foreach ($restOfWorld as $country) {
            $row = $this->appendProductPricingForCountry($row, $pricing, $country, true);
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
    protected function appendProductPricingForCountry(
        array $row,
        ProductPricing $pricing,
        Country $country,
        $isRow
    ) {

        $price = $this->getProductPricingForCountry($pricing, $country);
        // poland is only via FBA at the moment ....
        if ($country->iso_2_code == 'PL') {
            $row[] = $price->unit_fba_price;
            return $row;
        }

        $postage = $pricing->product->row_postage_method;
        if (in_array($country->iso_2_code, $this->euIso2Codes)) {
            $postage = $pricing->product->eu_postage_method;
        }
        if ($country->iso_2_code = 'GB') {
            $postage = $pricing->product->uk_postage_method;
        }

        if ($price) {
            $row[] = $price->unit_sf_price;
            $row[] = $postage;

            if (!$isRow) {
                $row[] = $price->unit_fba_price;
            }
        } else {
            $row[] = 'N/A';
            $row[] = $postage;
            if (!$isRow) {
                $row[] = 'N/A';
            }
        }
        return $row;
    }
}
