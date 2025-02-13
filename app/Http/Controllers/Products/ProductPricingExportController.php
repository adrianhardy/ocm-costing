<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Currency;
use App\Models\ProductPricing;
use App\Models\ProductPricingCountry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
            'ASIN',
            'APPROVER',
            'INITIALS',
            'QTY DELIVERED',
            'QTY TO LIST',
            'UK',
            'PP',
            'FBA',
            'FBA FEE',
            'TL/PIGGY',
            'WEBSITE',
            'EBAY',
            'EBAY QTY',
            'ADDED SS',
        ];

        foreach ($eu as $country) {
            // PL doesn't have SF or PP columns
            if ($country->iso_2_code == 'PL') {
                $headings[] = 'POLAND FBA';
            } else {
                $headings[] = strtoupper($country->name);
                $headings[] = 'PP';
                $headings[] = 'FBA';
                $headings[] = 'FBA FEE';
            }
        }
        // Row countries only have self fulfilled and PP cols
        // FBA is not used for ROW
        foreach ($restOfWorld as $country) {
            $headings[] = strtoupper($country->name);
            $headings[] = 'PP';
            $headings[] = 'FBA FEE';
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getSheet(0);
        $sheet->setTitle('Report');


        $currentWriterRow = 0;

        foreach($headings as $index => $heading) {
            $column = $index + 1;
            $sheet->setCellValueByColumnAndRow($column, $currentWriterRow + 1,  $heading);
            $sheet->getStyleByColumnAndRow($column, $currentWriterRow + 1)->applyFromArray([
                'font' => ['bold' => true, ]
            ]);

            foreach ($sheet->getColumnIterator() as $column) {
                $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
            }

        }

        $currentWriterRow++;

        foreach($pricings as $pricing) {
            $row = $this->createCsvRow(
                $pricing,
                $gb,
                $eu,
                $restOfWorld,
                $pricing->product->primary_sku,
                $pricing->product->primary_asin
            );

            $this->writeRow($row, $sheet, $currentWriterRow);

            $asins = $pricing->product->additional_asins;
            $skus = $pricing->product->additional_skus;

            if (is_array($asins) || is_array($skus)) {
                $biggestCount = max(count($asins), count($skus));
                $asins = array_pad($asins, $biggestCount, null);
                $skus = array_pad($skus, $biggestCount, null);

                if ($biggestCount > 0) {
                    foreach ($asins as $i => $asin) {
                        $currentWriterRow++;
                        $sku = $skus[$i]??null;
                        $row = $this->createCsvRow($pricing, $gb, $eu, $restOfWorld, $sku, $asin);

                        // make this a method or something
                        $this->writeRow($row, $sheet, $currentWriterRow);
                    }
                }

            }
            $currentWriterRow++;
            $pricing->exported_at = Carbon::now();
            $pricing->save();
        }

        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Instructions');

        $sheet->setCellValue('A1', setting('instructions'))
            ->getStyle('A1')
            ->getAlignment()
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        $sheet->getColumnDimension('A')->setWidth(250);
        $sheet->getRowDimension(1)->setRowHeight(500);

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        $writer->save($path = storage_path('productPricing.xlsx'));

        return response()->download($path)->deleteFileAfterSend();


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
                        $row = $this->createCsvRow($pricing, $gb, $eu, $restOfWorld, $sku, $asin);
                        $csv->fputcsv($row);
                    }
                }
            }
            $pricing->exported_at = Carbon::now();
            $pricing->save();
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
        $row[] = $asin;
        $row[] = $pricing->approver_id?$pricing->approver->initials:''; // Initials
        $row[] = ''; // BLANK SPACE - INITIALS OF WHO'S ASSIGNED TO DO THE JOB
        $row[] = ''; // qty delivered
        $row[] = ''; // qty to list
        $row = $this->appendProductPricingForCountry($row, $pricing, $gb, false);
        $row[] = ''; // TL/PIGGY
        $row[] = $pricing->website_price; // WEBSITE

        $ukPrice = $this->getProductPricingForCountry($pricing, $gb);

        if($ukPrice) {
            $row[] = $ukPrice->unit_sf_price; // EBAY
            $row[] = ''; // EBAY QTY
            $row[] = ''; // ADDED SS
        }

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
            $row[] = $price->unit_fba_price??'';
            return $row;
        }

        $postage = $pricing->product->row_postage_method;
        if (in_array($country->iso_2_code, $this->euIso2Codes)) {
            $postage = $pricing->product->eu_postage_method;
        }
        if ($country->iso_2_code == 'GB') {
            $postage = $pricing->product->uk_postage_method;
        }

        if ($price) {
            $row[] = $price->unit_sf_price;
            $row[] = $postage;

            if (!$isRow) {
                $row[] = $price->unit_fba_price??'';
            }

        } else {
            $row[] = 'N/A';
            $row[] = $postage;
            if (!$isRow) {
                $row[] = 'N/A';
            }
        }

        $row[] = number_format($price->unit_fba_fee??0, 2, '.', '');
        return $row;
    }

    /**
     * @param array $row
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param int $currentWriterRow
     * @return array
     */
    protected function writeRow(array $row, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $currentWriterRow): array
    {
        foreach ($row as $index => $field) {
            $column = $index + 1;
            $sheet->setCellValueByColumnAndRow($column, $currentWriterRow + 1, $field);
        }
        return array($index, $field, $column);
    }
}
