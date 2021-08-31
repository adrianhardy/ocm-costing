<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ProductHistoryController extends Controller
{
    /**
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Product $product)
    {
        $activity = $product->activities()->whereNotNull('properties')
            ->orderBy('created_at', 'DESC')
            ->get();

        return response()->json([
            'items' => $activity->map(function(Activity $log) {
                return $log->only([
                    'id', 'properties', 'updated_at'
                ]);
            })
        ]);
    }
}
