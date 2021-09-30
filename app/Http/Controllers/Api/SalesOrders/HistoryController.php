<?php

namespace App\Http\Controllers\Api\SalesOrders;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Transformers\VersionTransformer;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    /**
     * @param SalesOrder $salesOrder
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(SalesOrder $salesOrder)
    {
        $history = $salesOrder
            ->history()
            ->orderBy('created_at', 'DESC')
            ->get();

        return response()->json([
            'items' => fractal($history, new VersionTransformer())
        ]);
    }
}
