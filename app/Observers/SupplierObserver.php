<?php

namespace App\Observers;

use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SupplierObserver
{
    public function creating(Supplier $supplier){
        $supplier->created_at = Carbon::now();
        $supplier->updated_at = Carbon::now();
    }
    /**
     * @param Supplier $supplier
     */
    public function saving(Supplier $supplier)
    {
        $supplier->updated_at = Carbon::now();
    }
}