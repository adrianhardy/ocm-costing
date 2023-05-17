<?php

namespace App\Http\Controllers\Api\Uk\Invoices;

use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\Repositories\InvoiceLineRepository;
use App\Transformers\InvoiceLineTransformer;
use Illuminate\Http\Request;
use Prettus\Repository\Criteria\RequestCriteria;

class InvoiceLinesController extends Controller
{
    public function index(Request $request, InvoiceLineRepository $repository)
    {
        $paginator = $repository->pushCriteria(app(RequestCriteria::class));

        if($request->input('invoice_id')) {
            $paginator = $paginator->where('invoice_id', $request->input('invoice_id'));
        }

        if($request->input('product_id')) {
            $paginator = $paginator->whereHas('sales_order_line', function($query) use ($request) {
                return $query->where('product_id', $request->input('product_id'));
            })->whereHas('invoice', function($query) {
                return $query->whereNotNull('approved_at')->orWhere('is_approval_required', false);
            });
        }

        $paginator = $paginator->paginate($request->input('per_page', '25'));

        return response()->json(new SerializeLengthAwarePaginator($paginator, new InvoiceLineTransformer()));
    }
}
