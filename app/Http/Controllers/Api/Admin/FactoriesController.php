<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\Factory;
use App\Models\Repositories\FactoryRepository;
use App\Transformers\FactoryTransformer;
use Illuminate\Http\Request;

class FactoriesController extends Controller
{
    public function index(Request $request, FactoryRepository $factoryRepository)
    {
        $paginator = $factoryRepository;

        if (\Auth::user()->hasRole(['supplier'])) {
            $paginator->whereHas('users', function($query) {
                return $query->where('id', \Auth::id());
            });
        }

        $perPage = null;
        if ($request->input('all')) {
            $perPage = 999999;
        }
        $paginator = $paginator
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json(new SerializeLengthAwarePaginator(
            $paginator, new FactoryTransformer()
        ));
    }

    public function show(Factory $factory)
    {
        return response()->json(fractal($factory, new FactoryTransformer));
    }

    public function update(Request $request, Factory $factory)
    {
        $request->validate([
            'name' => 'required|max:255',
            'country' => 'required|max:255',
            'delivery_address' => 'sometimes|max:255',
            'contact_name' => 'sometimes|max:255',
            'contact_email' => 'sometimes|email|max:255',
            'contact_phone' => 'sometimes|max:64',
            'shipping_name' => 'sometimes|max:128',
            'products_description' => 'sometimes|max:128',
        ]);

        $factory->fill($request->only([
            'name',
            'country',
            'delivery_address',
            'contact_name',
            'contact_email',
            'contact_phone',
            'shipping_name',
            'products_description'
        ]));

        $factory->save();

        return response(fractal($factory->fresh(), new FactoryTransformer));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'country' => 'required|max:255',
            'delivery_address' => 'sometimes|max:255',
            'contact_name' => 'sometimes|max:255',
            'contact_email' => 'sometimes|email|max:255',
            'contact_phone' => 'required|max:64',
            'shipping_name' => 'sometimes|max:128',
            'products_description' => 'sometimes|max:128',
        ]);

        $factory = new Factory();

        $factory->fill($request->only([
            'name',
            'country',
            'delivery_address',
            'contact_name',
            'contact_email',
            'contact_phone',
            'shipping_name',
            'products_description'
        ]));

        $factory->save();

        return response(fractal($factory->fresh(), new FactoryTransformer));
    }
}
