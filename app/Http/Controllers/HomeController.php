<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request) {

        if (\Auth::user()->hasRole(['supplier'])) {
            return response()->redirectToRoute('sales-orders.index');
        }

        $orders[SalesOrder::STATUS_DRAFT] = SalesOrder::query()
            ->orderBy('created_at')
            ->currentStatus(SalesOrder::STATUS_DRAFT)
            ->get();

        $orders[SalesOrder::STATUS_QUOTE] = SalesOrder::query()
            ->orderBy('created_at')
            ->currentStatus(SalesOrder::STATUS_QUOTE)
            ->get();

        $orders[SalesOrder::STATUS_SALES_ORDER] = SalesOrder::query()
            ->orderBy('created_at')
            ->currentStatus(SalesOrder::STATUS_SALES_ORDER)
            ->get();

        $orders[SalesOrder::STATUS_CONFIRMED_SALES_ORDER] = SalesOrder::query()
            ->orderBy('created_at')
            ->currentStatus(SalesOrder::STATUS_CONFIRMED_SALES_ORDER)
            ->get();

        $orders['shipments'] = SalesOrder::query()
            ->orderBy('created_at')
            ->whereHas('consignments', function($query) {
                $query->where(function($query) {
                    $query->whereNull('docked_at')
                        ->orWhere('docked_at', '>', now()->subYears(5));
                });
            })
            ->get();

        $user = User::query()->first();
        $comments = $user->comments()
            ->orderBy('created_at', 'DESC')
            ->paginate(5);

        return response()->view('home', [
            'comments' => $comments,
            'orders' => $orders
        ]);
    }
}
