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

        $nonEuroEuCountries = Country::query()
            ->where('has_pricing', 1)
            ->orderBy('name')
            ->whereIn('iso_2_code', Country::NON_EURO_EU_COUNTRIES)
            ->get();

        $uk = Country::query()->where('iso_2_code', 'GB')->first();

        $row = Country::query()
            ->whereNotIn('iso_2_code', $euroCountries->pluck('iso_2_code'))
            ->whereNotIn('iso_2_code', Country::NON_EURO_EU_COUNTRIES)
            ->where('id', '!=', $uk->id)
            ->where('has_pricing', '1')
            ->orderByRaw('FIELD(iso_2_code, "US", "CA", "MX", "AU")')
            ->get();

        return view('products.pricelist.index', [
            'non_euro_eu_countries' => fractal($nonEuroEuCountries, new CountryTransformer())->parseIncludes('currency'),
            'euro_countries' => fractal($euroCountries, new CountryTransformer())->parseIncludes('currency'),
            'row_countries' => fractal($row, new CountryTransformer())->parseIncludes('currency'),
            'uk' => fractal($uk, new CountryTransformer())->parseIncludes('currency')
        ]);
    }
}
