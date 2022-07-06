<?php

namespace App\Http\Controllers\Api\Shipments;

use App\Http\Controllers\Controller;
use App\Models\Consignment;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\Models\Media;

class MediaController extends Controller
{
    public function store(Request $request, Consignment $shipment){
        $this->validate($request, [
            'file' => 'required|file|max:10000'
        ]);

        $shipment
            ->addMediaFromRequest('file')
            ->toMediaCollection();

        return response()->json([]);
    }

    public function destroy(Request $request, Consignment $shipment, Media $medium){
        $medium->delete();
        return response()->json();
    }

}
