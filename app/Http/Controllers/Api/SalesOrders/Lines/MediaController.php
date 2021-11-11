<?php

namespace App\Http\Controllers\Api\SalesOrders\Lines;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\Models\Media;

class MediaController extends Controller
{
    /**
     * @param SalesOrder $sales_order
     * @param SalesOrderLine $line
     * @param Media $media
     */
    public function store(Request $request, SalesOrder $sales_order, SalesOrderLine $line)
    {

        /** @var Media $media */
        $media = Media::query()->findOrFail($request->input('media_id'));
        if (!$line->product->getMedia()->where('id', $media->id)) {
            abort(400, 'This image does not belong to this product');
        }
        $line->selected_media()->associate($media);
        $line->save();
        return response()->json();
    }
}
