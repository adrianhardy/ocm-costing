<?php

namespace App\Http\Controllers\Admin\Imports;
use App\Http\Controllers\Controller;
use App\Models\Factory;
use App\Models\Import;
use App\Models\ImportMapping;
use App\Models\Supplier;
use App\Transformers\ImportTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RunController extends Controller
{

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        if (!$request->query('code')) {
            return response()->redirectToRoute('imports.create', ['code' => Str::random(32)]);
        }

        return response()->view('admin.imports.create', [
            'columns' => [
                'products' => config('imports.products.columns'),
            ],
            'code' => $request->query('code'),
            'suppliers' => Supplier::query()->orderBy('name')->get()->map(function($supplier) {
                return $supplier->only(['id', 'name']);
            }),
            'types' => [
                'products'  => 'Products',
            ]
        ]);
    }


    /**
     * Create a new import in the database. Update the mapping if a mapping name
     * has been provided.
     *
     * Certain types of import will run immediately.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Spatie\ModelStatus\Exceptions\InvalidStatus
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'supplier.id'        => 'required|exists:suppliers,id',
            'mapping_name'       => 'nullable|min:0|max:100',
            'skip_rows'          => 'sometimes|min:0',
//            'factory.id'         => 'required|exists:factories,id'
        ]);

        $skipRows       = $request->input('skip_rows', 0);
        $mappingName    = $request->input('mapping_name');
        $csv            = $request->file('csv');
        $supplier       = Supplier::query()->findOrFail($request->input('supplier.id'));
        $factory       =  Factory::query()->findOrFail($request->input('factory.id'));

        DB::beginTransaction();

        if ($mappingName) {
            /** @var ImportMapping $mapping */
            $importMapping = ImportMapping::query()->where('name', $mappingName)->firstOrNew([
                'name' => $mappingName
            ]);
            $importMapping->fill($request->only(['type', 'mapping']));
            $importMapping->save();
        }

        $path = date('Y/m');
        $filename = Storage::disk('imports')->putFile($path, $csv);
        $import = new Import([
            'path'          => $filename,
            'total_rows'    => 0,
            'total_errors'  => 0,
            'current_line'  => 0,
            'type'          => 'products',
            'mapping'       => $request->input('mapping'),
            'config'        => [
                'supplier_id'       => $supplier->id,
                'supplier'          => $supplier->only(['id', 'name']),
                'factory_id'        => $factory->id,
                'factory'           => $factory->only(['id', 'name']),
            ],
            'skip_header_rows' => intval($skipRows),
            'log_path'      => $filename . ".log"
        ]);
        $import->creator()->associate(\Auth::user());
        $import->save();
        $import->setStatus(Import::STATUS_PENDING);

        DB::commit();
        set_time_limit(0);
        Artisan::call("import:products", ['import' => $import->id]);
        return response()->json(
            fractal($import->fresh(), new ImportTransformer()),
            200
        );

    }
}
