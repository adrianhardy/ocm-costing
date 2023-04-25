<?php

namespace App\Http\Controllers\Uk\Imports;

use App\Http\Controllers\Controller;
use App\Models\Import;
use App\Models\SalesOrder;
use App\Transformers\ImportTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class OrderAcknowledgmentLinesImportController extends Controller
{
    public function index(SalesOrder $orderAcknowledgement){
        return view('uk.order-acknowledgements.import.index', compact(['orderAcknowledgement']));
    }

    public function store(Request $request, SalesOrder $orderAcknowledgement){
        $this->validate($request, [
            'file' => 'required|file',
            'skip' => 'required|boolean',
        ]);

        $csv = $request->file('file');
        $path = date('Y/m');
        $filename = Storage::disk('imports')->putFile($path, $csv);
        $import = new Import([
            'path'          => $filename,
            'total_rows'    => 0,
            'total_errors'  => 0,
            'current_line'  => 0,
            'type'          => 'oa_lines',
            'skip_header_rows' => intval($request->input('skip')),
            'log_path'      => $filename . ".log"
        ]);

        $import->creator()->associate(auth()->user());
        $import->mapping = [];
        $import->config = [
            'oa_id' => $orderAcknowledgement->id
        ];

        $import->save();
        $import->setStatus(Import::STATUS_PENDING);


        Artisan::call('import:oa-lines', [
            'import' => $import->id,
            '--skip-first-row' => $import->skip_header_rows,
        ]);

        return response()->json(fractal($import->fresh(), new ImportTransformer())->parseIncludes('log'));
    }


}
