<?php

namespace App\Http\Controllers\Uk\Pricing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DailyPricingController extends Controller
{
    public function index(){
        return view('uk.daily-pricing.index');
    }
}
