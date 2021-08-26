<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\Models\Media;

class MediaController extends Controller
{
    public function destroy(Media $medium)
    {
        $medium->delete();
        return response()->json();
    }


    /**
     * @param Request $request
     * @param Media $medium
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Media $medium)
    {
        return $medium->toResponse($request);
    }
}
