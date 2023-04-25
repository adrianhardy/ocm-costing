<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ProductRafStockController extends Controller
{
    public function index(Request $request, Product $product)
    {
        $dates = [];

        $client = new Client(['base_uri' => env('RAF_BASE_URI', 'http://127.0.0.1:8000')]);

        for($i = 0; $i < 13; $i++) {

            $date = Carbon::now()->subMonths($i * 3);

            $response = $client->request('GET', '/api/stock-export', [
                'query' => [
                    'items' => [$product->item_number],
                    'selected_date' => $date->toDateString()
                ]]
            );

            $response = json_decode($response->getBody());

            $total = 0;
            foreach($response as $stock) {
                if($stock->item->item_number === $product->item_number) {
                    $total = $stock->level;
                };
            }

            $dates[] = [
                'date' => $date,
                'stock' => $total
            ];

        }

        return response()->json(['items' => $dates]);
    }
}
