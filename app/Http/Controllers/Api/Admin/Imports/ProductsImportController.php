<?php

namespace App\Http\Controllers\Api\Admin\Imports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ProductsImportController extends Controller
{
    public function store(Request $request){
        $this->validate($request, [
            'file' => 'required|file',
            'skip' => 'required|boolean'
        ]);

        $csv = file_get_contents($request->file()['file']->getRealPath());
        $skip = $request->input('skip');
//
//        Artisan::call('import:products', [
//            'csv' => $csv, '--skip-first-row' => $skip
//        ]);
    }
}
