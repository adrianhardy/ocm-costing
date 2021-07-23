<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\Repositories\ProductRepository;
use App\Transformers\ProductTransformer;
use Illuminate\Http\Request;

class ProductsController extends Controller
{

    /**
     * @param Request $request
     * @param ProductRepository $productRepository
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, ProductRepository $productRepository)
    {
        $paginator = $productRepository->paginate();
        return response()->json(new SerializeLengthAwarePaginator(
            $paginator, new ProductTransformer
        ));
    }
}
