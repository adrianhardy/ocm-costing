<?php

namespace App\Http\Controllers\Admin\Currencies;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index(Request $request)
    {
        return response()->view('admin.currencies.index');
    }
}
