<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SuppliersController extends Controller
{
    public function index(){
        return view('admin.suppliers.index');
    }

    public function create(){
        return view('admin.suppliers.create');
    }

    public function edit(Supplier $supplier){
        return view('admin.suppliers.edit', [
            'supplier' => $supplier
        ]);
    }
}
