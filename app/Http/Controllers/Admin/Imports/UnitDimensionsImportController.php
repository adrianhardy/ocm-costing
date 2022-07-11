<?php

namespace App\Http\Controllers\Admin\Imports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UnitDimensionsImportController extends Controller
{
    public function index(){
        return view('admin.imports.unit-dimensions.index');
    }
}
