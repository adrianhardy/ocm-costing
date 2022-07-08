<?php

namespace App\Http\Controllers\Api\Admin\Imports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ProductASINImportController extends Controller
{
    public function store(Request $request){
        $this->validate($request, [
            'file' => 'required|file',
            'skip' => 'required|boolean'
        ]);

        $csv = $request->file()['file'];
        $skip = $request->input('skip');

        Artisan::call('import:asin', [
            'csv' => $csv, '--skip-first-row' => $skip,
        ]);
    }
}
