<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Transformers\CountryTransformer;
use Illuminate\Http\Request;

class ProductPricingController extends Controller
{
    public function index()
    {
        $euroCountries = Country::query()
            ->whereHas('currency', function($query) {
                $query->where('code', 'EUR');
            })
            ->where('has_pricing', 1)
            ->orderBy('name')
            ->get();

        $uk = Country::query()->where('iso_2_code', 'GB')->first();

        $row = Country::query()
            ->whereNotIn('iso_2_code', $euroCountries->pluck('iso_2_code'))
            ->where('id', '!=', $uk->id)
            ->where('has_pricing', '1')
            ->orderBy('name')
            ->get();

        return view('products.pricelist.index', [
            'euro_countries' => fractal($euroCountries, new CountryTransformer())->parseIncludes('currency'),
            'row_countries' => fractal($row, new CountryTransformer())->parseIncludes('currency'),
            'uk' => fractal($uk, new CountryTransformer())->parseIncludes('currency')
        ]);
    }
}
