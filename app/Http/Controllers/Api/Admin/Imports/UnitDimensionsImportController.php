<?php

namespace App\Http\Controllers\Api\Admin\Imports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class UnitDimensionsImportController extends Controller
{
    public function store(Request $request){
        $this->validate($request, [
            'file' =>'required|file',
            'skip' => 'required|boolean'
        ]);

        $csv = file_get_contents($request->file()['file']->getRealPath());
        $skip = $request->input('skip');

        dd($skip);


        // we have the csv here, so this is your 'insert code here'
        // I imagine it'll be something like this?


//        Artisan::call('import:dimensions', [
//            'csv' => $csv, '--skip-first-row' => $request->input('skip_first_row')
//        ]);
    }
}
