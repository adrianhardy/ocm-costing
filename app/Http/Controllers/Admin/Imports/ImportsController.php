<?php

namespace App\Http\Controllers\Admin\Imports;

use App\Http\Controllers\Controller;
use App\Models\Import;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImportsController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->view('admin.imports.index');
    }

    /**
     * @param Import $import
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function show(Import $import)
    {
        return response()->download(
            Storage::disk('imports')->path($import->log_path),
            'log.log'
        );
    }
}
