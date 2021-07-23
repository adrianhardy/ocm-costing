<?php

namespace App\Http\Controllers\Api\Calculator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Domain;

class SimpleShippingCalculator extends Controller
{

    public function store(Request $request)
    {
        $this->validate($request, [
            'case.width'                => 'required|numeric|min:0',
            'case.depth'                => 'required|numeric|min:0',
            'case.height'               => 'required|numeric|min:0',

            'cost_to_ship_per_cubic_unit'   => 'required|numeric|min:0',
            'cost_to_ship_system'           => 'required|in:imperial,metric',
            'units_per_case'                => 'required|numeric|min:0',
            'unit_price'                    => 'required|numeric|min:0',
            'import_duty'                   => 'required|numeric|min:0',

            // commission
            'unit_price_commission'         => 'required|numeric|min:0',
            'currency_conversion_rate'      => 'required|numeric|min:0',
        ]);

        $calculator = new Domain\SimpleShippingCalculator(
            $request->input('cost_to_ship_per_cubic_unit'),
            $request->input('cost_to_ship_system')
        );
        $calculator->setCaseVol(new Domain\Dimensions(
            $request->input('case.width'),
            $request->input('case.height'),
            $request->input('case.depth')
        ));

        $calculator
                ->setUnitPrice($request->input('unit_price'))
                ->setUnitsPerCase($request->input('units_per_case'));

        $calculator->calculateTotalCostPerUnit();

    }
}
