<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Factory;
use Illuminate\Http\Request;

class FactoriesController extends Controller
{
    public function index(){
        return view('admin.factories.index');
    }

    public function create(){
        return view('admin.factories.create');
    }

    public function edit(Factory $factory){
        return view('admin.factories.edit', [
            'factory' => $factory
        ]);
    }
}
