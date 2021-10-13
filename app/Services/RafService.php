<?php


namespace App\Services;


use App\Models\InspectionReport;
use App\Models\Product;
use GuzzleHttp\Client;

class RafService
{

    /**
     * @var Client
     */
    protected $http;


    public function __construct()
    {
        $this->http = new Client([
            'base_uri' => config('raf.base_uri'),
            'headers' => [
                'Authorization' => 'Bearer ' . config('raf.api_key')
            ]
        ]);
    }

    /**
     * @param Product $product
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createProduct(Product $product)
    {
        $json = $this->serializeProduct($product, true);

        // make json representation of product ready for RAF
        try {
            /*
            $this->http->post('/api/costing/integration/products', [
                'json' => $json,
            ]);
            */
        } catch (\Exception $ex) {
            // do something clever here
            throw $ex;
        }
    }

    /**
     * @param InspectionReport $inspection
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateProductForced(InspectionReport $inspection)
    {
        $json = $this->serializeProduct($inspection->sales_order_line->product, false);

        $json = array_merge($json, $inspection->only([
            'inner_qty',
            'case_weight_kg',
            'unit_weight_kg',
        ]));

        $json['case_qty'] = $inspection->units_per_case;
        $json['case_length_cm'] = $inspection->case_length_m;
        $json['case_width_cm'] = $inspection->case_width_m;
        $json['case_height_cm'] = $inspection->case_height_m;



        // make json representation of product ready for RAF
        /*
        $this->http->put('api/costing/integration/products', [
            'json' => $json,
            'item_number' => $json['item_number']
        ]);
	*/
    }

    /**
     * @param Product $product
     * @return array
     */
    public function serializeProduct(Product $product, $creating): array
    {
        $json = $product->only([
            'name',
            'item_number',
            'case_weight_kg',
            'unit_weight_kg',
            'inner_qty',
        ]);

        if($creating){
            $media = $product->getFirstMedia();
            if ($media) {
                $blob = base64_encode(file_get_contents($media->getPath()));
            } else {
                $blob = null;
            }
            $json['media'] = $blob;
        }

        $json['case_qty'] = $product->units_per_case;
        $json['case_length_cm'] = $product->length_m * 100;
        $json['case_width_cm'] = $product->width_m * 100;
        $json['case_height_cm'] = $product->height_m * 100;
        $json['supplier'] = $product->supplier->name;
        return $json;
    }


}
