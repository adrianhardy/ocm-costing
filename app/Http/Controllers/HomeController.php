<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request) {

        if (\Auth::user()->hasRole(['supplier'])) {
            return response()->redirectToRoute('sales-orders.index');
        }

        $years = range(date('Y'), date('Y')-10);


        $year = $request->input('year', date('Y'));
        $start = Carbon::createFromDate($year)->startOfYear();
        $end = $start->copy()->endOfYear();

        $orders[SalesOrder::STATUS_DRAFT] = SalesOrder::query()
            ->orderBy('created_at')
            ->currentStatus(SalesOrder::STATUS_DRAFT)
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $orders[SalesOrder::STATUS_QUOTE] = SalesOrder::query()
            ->orderBy('created_at')
            ->currentStatus(SalesOrder::STATUS_QUOTE)
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $orders[SalesOrder::STATUS_SALES_ORDER] = SalesOrder::query()
            ->orderBy('created_at')
            ->currentStatus(SalesOrder::STATUS_SALES_ORDER)
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $orders[SalesOrder::STATUS_CONFIRMED_SALES_ORDER] = SalesOrder::query()
            ->orderBy('created_at')
            ->currentStatus(SalesOrder::STATUS_CONFIRMED_SALES_ORDER)
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $orders['shipments'] = SalesOrder::query()
            ->orderBy('created_at')
            ->whereBetween('created_at', [$start, $end])
            ->whereHas('lines', function($query) {
                $query->whereHas('consignments', function ($query) {
                    $query->whereNull('deleted_at');
                });
            })
            ->get();

        $user = User::query()->first();
        $comments = $user->comments()
            ->orderBy('created_at', 'DESC')
            ->paginate(5);

        return response()->view('home', [
            'comments' => $comments,
            'orders' => $orders,
            'years' => $years,
            'current_year' => $year
        ]);
    }
}
