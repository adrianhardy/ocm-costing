<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\AmazonGuidance;
use App\Models\Consignment;
use App\Models\Factory;
use App\Models\Product;
use App\Models\Repositories\MediaRepository;
use App\Models\Supplier;
use App\Transformers\MediaTransformer;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class DocumentsController extends Controller
{
    public function index(Request $request, MediaRepository $mediaRepository)
    {
        $paginator = $mediaRepository->where('model_type','!=',Product::class)->where('model_type','!=', AmazonGuidance::class);

        switch($request->input('type')) {
            case 'shipment':
                $paginator = $paginator->where('model_type', Consignment::class);
                break;
            case 'factory':
                $paginator = $paginator->where('model_type', Factory::class);
                break;
            case 'supplier':
                $paginator = $paginator->where('model_type', Supplier::class);
                break;
        }

        $paginator = $paginator->paginate($request->input('per_page', 100));

        return response()->json(new SerializeLengthAwarePaginator($paginator, new MediaTransformer()));
    }
}
