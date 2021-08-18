<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\Models\Media;

class MediaController extends Controller
{

    /**
     * @param Request $request
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Product $product)
    {
        $this->validate($request, [
            'image' => 'required|file|max:10000|image|dimensions:max_width=2500,max_height=2500'
        ], [
            'dimensions' => 'The image is too large. Please resize to a maximum of 2500x2500'
        ]);

        $product
            ->addMediaFromRequest('image')
            ->toMediaCollection('images');

        return response()->json([]);
    }

    /**
     * @param Product $product
     * @param Media $media
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(Product $product, Media $medium)
    {
        abort_unless(
            $medium->model == $product,
            400,
            'Media does not belong to product'
        );
        $medium->delete();
        return response()->json();
    }
}
