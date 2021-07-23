<?php

namespace App\Http\Controllers\Api;

use App\Fractal\DataArraySerializer;
use App\Http\Controllers\Controller;
use App\Models\ImportMapping;
use App\Transformers\ImportMappingTransformer;
use Illuminate\Http\Request;

class ImportMappingsController extends Controller
{
    public function index(Request $request)
    {
        $query = ImportMapping::query()->orderBy('name');
        if ($request->input('type')) {{
            $query->where('type', $request->input('type'));
        }}
        return response()->json([
            'items' => fractal(
                $query->get(),
                new ImportMappingTransformer,
                new DataArraySerializer()
            )
        ]);
    }
}
