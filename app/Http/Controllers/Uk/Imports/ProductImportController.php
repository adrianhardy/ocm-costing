<?php

namespace App\Http\Controllers\Uk\Imports;

use App\Http\Controllers\Controller;
use App\Models\Import;
use App\Models\Supplier;
use App\Transformers\ImportTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class ProductImportController extends Controller
{
    public function import(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|file',
            'skip' => 'required|boolean',
            'supplier_id' => 'required'
        ]);

        abort_unless($request->file('file')->clientExtension() == 'csv', 400, 'You must supply a CSV file');

        $csv = $request->file('file');
        $supplier       = Supplier::query()->findOrFail($request->input('supplier_id'));

        $path = date('Y/m');
        $filename = Storage::disk('imports')->putFile($path, $csv);
        $import = new Import([
            'path'          => $filename,
            'total_rows'    => 0,
            'total_errors'  => 0,
            'current_line'  => 0,
            'type'          => 'uk-product',
            'skip_header_rows' => intval($request->input('skip')),
            'log_path'      => $filename . ".log"
        ]);

        $import->creator()->associate(auth()->user());
        $import->mapping = [];
        $import->config = [
            'supplier_id' => $supplier->id
        ];
        $import->save();
        $import->setStatus(Import::STATUS_PENDING);

        Artisan::call('import:products:uk', [
            'import' => $import->id,
            '--skip-first-row' => $import->skip_header_rows,
            '--pretend' => false
        ]);

        return response()->json(fractal($import->fresh(), new ImportTransformer())->parseIncludes('log'));
    }
}
