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
    public function index(FactoryRepository $factoryRepository)
    {
        $paginator = $factoryRepository->paginate();
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
            'delivery_address' => 'required|max:255',
            'contact_name' => 'required|max:255',
            'contact_email' => 'required|max:255',
            'contact_phone' => 'required|max:64'
        ]);

        $factory->fill($request->only([
            'name',
            'country',
            'delivery_address',
            'contact_name',
            'contact_email',
            'contact_phone'
        ]));

        $factory->save();

        return response(fractal($factory->fresh(), new FactoryTransformer));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'country' => 'required|max:255',
            'delivery_address' => 'required|max:255',
            'contact_name' => 'required|max:255',
            'contact_email' => 'required|max:255',
            'contact_phone' => 'required|max:64'
        ]);

        $factory = new Factory();

        $factory->fill($request->only([
            'name',
            'country',
            'delivery_address',
            'contact_name',
            'contact_email',
            'contact_phone'
        ]));

        $factory->save();

        return response(fractal($factory->fresh(), new FactoryTransformer));
    }
}
