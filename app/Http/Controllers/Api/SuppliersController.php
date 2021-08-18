<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\Repositories\SupplierRepository;
use App\Models\Supplier;
use App\Transformers\SupplierTransformer;
use Illuminate\Http\Request;

class SuppliersController extends Controller
{

    /**
     * @param Request $request
     * @param SupplierRepository $supplierRepository
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(
        Request $request,
        SupplierRepository $supplierRepository
    )
    {
        if ($request->input('all')) {
            $suppliers = $supplierRepository->get();
            return response()->json([
                'items' => fractal($suppliers, new SupplierTransformer())
            ]);
        }

        $suppliers = $supplierRepository->paginate(
            $request->input('per_page', config('app.default_results_per_page'))
        );
        return response()->json(
            new SerializeLengthAwarePaginator($suppliers, new SupplierTransformer())
        );
    }
}
