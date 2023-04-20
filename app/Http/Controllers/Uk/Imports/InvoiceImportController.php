<?php

namespace App\Http\Controllers\Uk\Imports;

use App\Http\Controllers\Controller;
use App\Models\Import;
use App\Models\SalesOrder;
use App\Transformers\ImportTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class InvoiceImportController extends Controller
{
    public function index(SalesOrder $orderAcknowledgement) {
        return view('uk.invoices.import.index', compact(['orderAcknowledgement']));
    }

    public function store(Request $request, SalesOrder $orderAcknowledgement)
    {
        $this->validate($request, [
            'file' => 'required|file',
            'skip' => 'required|boolean',
            'reference' => 'required|unique:invoices,reference'
        ]);

        $csv = $request->file('file');
        $path = date('Y/m');
        $filename = Storage::disk('imports')->putFile($path, $csv);
        $import = new Import([
            'path'          => $filename,
            'total_rows'    => 0,
            'total_errors'  => 0,
            'current_line'  => 0,
            'type'          => 'invoice',
            'skip_header_rows' => intval($request->input('skip')),
            'log_path'      => $filename . ".log"
        ]);

        $import->creator()->associate(auth()->user());
        $import->mapping = [];
        $import->config = [
            'oa_id' => $orderAcknowledgement->id,
            'reference' => $request->input('reference')
        ];

        $import->save();
        $import->setStatus(Import::STATUS_PENDING);


        Artisan::call('import:invoice', [
            'import' => $import->id,
            '--skip-first-row' => $import->skip_header_rows,
            '--pretend' => true
        ]);

        return response()->json(fractal($import->fresh(), new ImportTransformer())->parseIncludes('log'));

    }
}
