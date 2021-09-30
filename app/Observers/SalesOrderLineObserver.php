<?php

namespace App\Observers;

use App\Models\SalesOrderLine;

class SalesOrderLineObserver
{
    public function saving(SalesOrderLine $line)
    {
        if ($line->isDirty('product_id')) {
            $line->fill($line->product->only([
                'units_per_case',
                'inner_qty',
                'case_weight_kg',
                'height_m',
                'length_m',
                'width_m',
                'unit_width_m',
                'unit_length_m',
                'unit_height_m',
                'unit_weight_kg',
                'barcode'
            ]));
        }
    }
}
