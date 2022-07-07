<?php

namespace App\Http\Controllers\Admin\Imports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductImportController extends Controller
{
    public function index(){
        return view('admin.imports.products.index');
    }
}
