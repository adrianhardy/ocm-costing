<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\Factory;
use App\Models\Product;
use App\Models\Repositories\ProductRepository;
use App\Models\Supplier;
use App\Transformers\ProductTransformer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductsController extends Controller
{
    /**
     * @param Request $request
     * @param Product $product
     * @return \Spatie\Fractal\Fractal
     */
    public function show(Request $request, Product $product)
    {
        $product->load('comments', 'comments.commentator', 'media');
        return fractal($product, new ProductTransformer());
    }


    /**
     * @param Request $request
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Product $product)
    {
        $unique = Rule::unique('products', 'item_number')
            ->whereNotNull('barcode')
            ->where('barcode', $request->input('barcode'))
            ->ignoreModel($product);


        $this->validate($request, [
            'supplier.id'           => 'required|exists:suppliers,id',
            'factory.id'            => 'sometimes|exists:factories,id',
            'item_number'           => ['required', 'min:1', 'max:64', $unique],
            'article_number'        => ['required', 'min:1', 'max:64'],
            'name'                  => 'required|min:1|max:200',
            'barcode'               => 'nullable|sometimes|max:32',
            'length_cm'             => 'nullable|sometimes|min:0|numeric',
            'width_cm'              => 'nullable|sometimes|min:0|numeric',
            'height_cm'             => 'nullable|sometimes|min:0|numeric',
            'unit_length_cm'        => 'nullable|sometimes|min:0|numeric',
            'unit_width_cm'         => 'nullable|sometimes|min:0|numeric',
            'unit_height_cm'        => 'nullable|sometimes|min:0|numeric',
            'case_weight_kg'        => 'nullable|sometimes|min:0|numeric',
            'unit_weight_kg'        => 'nullable|sometimes|min:0|numeric',
            'unit_price'            => 'nullable|sometimes|min:0|numeric',
            'units_per_case'        => 'nullable|sometimes|min:0|numeric',
            'inner_qty'             => 'nullable|sometimes|min:0|numeric',
            'packing_instructions'  => 'nullable|sometimes|max:500',
            'product_description'   => 'nullable|sometimes|max:500',
            'uk_postage_method'     => 'nullable|sometimes',
            'eu_postage_method'     => 'nullable|sometimes',
            'row_postage_method'    => 'nullable|sometimes'
        ]);

        $product->fill($request->only([
            'item_number',
            'article_number',
            'name',
            'packing_instructions',
            'barcode',
            'length_cm',
            'width_cm',
            'height_cm',
            'unit_length_cm',
            'unit_width_cm',
            'unit_height_cm',
            'units_per_case',
            'inner_qty',
            'packing_instructions',
            'product_description',
            'unit_price',
            'case_weight_kg',
            'unit_weight_kg',
            'is_excluded_from_commission',
            'uk_postage_method',
            'eu_postage_method',
            'row_postage_method'
        ]), [], [
            'supplier.id' => 'Supplier',
            'factory.id' => 'Factory'
        ]);
        $product->supplier()->associate(Supplier::find($request->input('supplier.id')));
        $product->factory()->associate((Factory::find($request->input('factory.id'))));
        $product->save();
        return response()->json(fractal($product->fresh(), new ProductTransformer()));
    }

    /**
     * @param Request $request
     * @return \Spatie\Fractal\Fractal
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $unique = Rule::unique('products', 'item_number')
            ->whereNotNull('barcode')
            ->where('barcode', $request->input('barcode'));

        $this->validate($request, [
            'supplier.id'           => 'required|exists:suppliers,id',
            'factory.id'            => 'sometimes|exists:factories,id',
            'item_number'           => ['required', 'min:1', 'max:64', $unique],
            'article_number'        => 'required|min:1|max:64',
            'name'                  => 'required|min:1|max:200',
            'barcode'               => 'sometimes|max:32',
            'length_cm'             => 'sometimes|min:0|numeric',
            'width_cm'              => 'sometimes|min:0|numeric',
            'height_cm'             => 'sometimes|min:0|numeric',
            'case_weight_kg'        => 'sometimes|min:0|numeric',
            'unit_price'            => 'sometimes|min:0|numeric',
            'units_per_case'        => 'sometimes|min:0|numeric',
            'inner_qty'             => 'sometimes|min:0|numeric',
            'packing_instructions'  => 'sometimes|max:500',
            'product_description'   => 'sometimes|max:500',
        ], [
            'item_number.unique' => 'The item number/barcode combination has already been taken'
        ], [
            'supplier.id' => 'Supplier',
            'factory.id' => 'Factory'
        ]);
        $product = new Product($request->only([
            'item_number',
            'article_number',
            'name',
            'barcode',
            'length_cm',
            'width_cm',
            'height_cm',
            'units_per_case',
            'inner_qty',
            'packing_instructions',
            'product_description',
            'unit_price',
            'case_weight_kg',
            'is_excluded_from_commission'
        ]));
        $product->supplier()->associate(Supplier::find($request->input('supplier.id')));
        $product->factory()->associate((Factory::find($request->input('factory.id'))));
        $product->save();
        return fractal($product->fresh(), new ProductTransformer());
    }

    /**
     * @param Request $request
     * @param ProductRepository $productRepository
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, ProductRepository $productRepository)
    {
        $paginator = $productRepository->paginate(
            $request->input('per_page', 100)
        );
        return response()->json(new SerializeLengthAwarePaginator(
            $paginator, new ProductTransformer
        ));
    }

    /**
     * @param Request $request
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function destroy(Request $request, Product $product)
    {
        $product->delete();
        return response()->json();
    }
}
