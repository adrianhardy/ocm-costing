<?php

namespace App\Observers;

use App\Models\Factory;
use Carbon\Carbon;
use Illuminate\Support\Str;

class FactoryObserver
{
    public function creating(Factory $factory)
    {
        $factory->created_at = Carbon::now();
        $factory->updated_at = Carbon::now();
    }

    public function saving(Factory $factory)
    {
        $factory->updated_at = Carbon::now();
    }
}