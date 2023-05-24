<?php

namespace App\Http\Controllers\Api\Countries;

use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\Country;
use App\Transformers\CountryTransformer;
use Illuminate\Http\Request;

class CountriesController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $paginator = Country::query()
            ->orderBy('name');

        if($request->input('amazon') === "true"){
            $paginator = $paginator->where('has_pricing', true);
        }

        if($request->input('all')) {
            return response()->json([
                'items' => fractal($paginator->get(), new CountryTransformer())
            ]);
        };

        $paginator = $paginator->paginate($request->input('per_page', 15));

        return response()->json(new SerializeLengthAwarePaginator(
            $paginator, new CountryTransformer()
        ));
    }

    /**
     * @param Request $request
     * @param Country $country
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Country $country)
    {
        $this->validate($request, [
            'has_pricing' => 'sometimes|boolean'
        ]);

        if ($request->input('has_pricing')) {
            $country->has_pricing = $request->input('has_pricing');
            $country->save();
        }

        if ($country->has_pricing && $request->input('has_pricing', null) === false) {
            $country->has_pricing = false;
            $country->save();
        }

        return response()->json();
    }
}
