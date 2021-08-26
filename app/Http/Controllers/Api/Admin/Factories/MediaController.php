<?php

namespace App\Http\Controllers\Api\Admin\Factories;

use App\Http\Controllers\Controller;
use App\Models\Factory;
use App\Models\Supplier;
use App\Transformers\FileTransformer;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function index(Factory $factory)
    {
        $files = $factory->getMedia('files');
        return response()->json([
            'items' => fractal($files, new FileTransformer())
        ]);
    }

    public function store(Request $request, Factory $factory)
    {
        $this->validate($request, [
            'file' => 'file|max:50000'
        ]);

        $factory->addMediaFromRequest('file')
            ->toMediaCollection('files', 'files');

        return response([
            'items' => fractal($factory->getMediaCollection('files'), new FileTransformer())
        ]);
    }
}
