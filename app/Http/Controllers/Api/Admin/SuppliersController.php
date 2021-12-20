<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\Currency;
use App\Models\Repositories\SupplierRepository;
use App\Models\Supplier;
use App\Transformers\SupplierTransformer;
use Illuminate\Http\Request;

class SuppliersController extends Controller
{
    public function index(SupplierRepository $supplierRepository)
    {
        $paginator = $supplierRepository->paginate();
        return response()->json(new SerializeLengthAwarePaginator(
            $paginator, new SupplierTransformer()
        ));
    }

    public function show(Supplier $supplier)
    {
        return response()->json(fractal($supplier, new SupplierTransformer));
    }

    public function update(Supplier $supplier, Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'country' => 'required|max:255',
            'delivery_address' => 'sometimes|max:255',
            'contact_name' => 'sometimes|max:255',
            'contact_email' => 'sometimes|max:255',
            'contact_phone' => 'sometimes|max:64',
            'currency.id' => 'required|exists:currencies,id'
        ]);

        $supplier->fill($request->only([
            'name',
            'country',
            'delivery_address',
            'contact_name',
            'contact_email',
            'contact_phone'
        ]));

        $supplier->currency()->associate(Currency::query()->findOrFail($request->input('currency.id')));
        $supplier->save();

        return response(fractal($supplier->fresh(), new SupplierTransformer));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'country' => 'required|max:255',
            'delivery_address' => 'sometimes|max:255',
            'contact_name' => 'sometimes|max:255',
            'contact_email' => 'sometimes|max:255',
            'contact_phone' => 'required|max:64',
            'currency.id' => 'required|exists:currencies,id'
        ]);

        $supplier = new Supplier();

        $supplier->fill($request->only([
            'name',
            'country',
            'delivery_address',
            'contact_name',
            'contact_email',
            'contact_phone'
        ]));
        $supplier->currency()->associate(Currency::query()->findOrFail($request->input('currency.id')));

        $supplier->save();

        return response(fractal($supplier->fresh(), new SupplierTransformer()));
    }
}
