<?php

namespace App\Http\Controllers\Api\Admin\Suppliers;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Transformers\FileTransformer;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function index(Supplier $supplier)
    {
        $files = $supplier->getMedia('files');
        return response()->json([
            'items' => fractal($files, new FileTransformer())
        ]);
    }

    public function store(Request $request, Supplier $supplier)
    {
        $this->validate($request, [
            'file' => 'file|max:50000'
        ]);

        $supplier->addMediaFromRequest('file')
            ->toMediaCollection('files', 'files');

        return response([
            'items' => fractal($supplier->getMediaCollection('files'), new FileTransformer())
        ]);
    }
}
