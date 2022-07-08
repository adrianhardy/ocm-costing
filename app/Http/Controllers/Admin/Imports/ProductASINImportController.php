<?php

namespace App\Http\Controllers\Admin\Imports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductASINImportController extends Controller
{
    public function index(){
        return view('admin.imports.product-asin.index');
    }
}
