<?php

namespace App\Http\Controllers\Api\Products;

use App\Domain\Export\ProductExport;
use App\Events\ProductInformationChanged;
use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\Factory;
use App\Models\Product;
use App\Models\Repositories\ProductRepository;
use App\Models\SalesOrder;
use App\Models\Supplier;
use App\Transformers\ProductTransformer;
use Carbon\Carbon;
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
        $this->authorize('view', $product);
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

        $this->authorize('update', $product);

        $unique = Rule::unique('products', 'item_number')
            ->whereNotNull('barcode')
            ->where('barcode', $request->input('barcode'))
            ->whereNull('deleted_at')
            ->ignoreModel($product);

        $uniqueBarcode = Rule::unique('products', 'barcode')->ignoreModel($product)
            ->whereNull('deleted_at');

        $supplierRequired = \Auth::user()->hasRole(['supplier'])?'nullable|sometimes|':'required';

        $this->validate($request, [
            'supplier.id'           => $supplierRequired . '|exists:suppliers,id',
            'factories.*.id'        => 'nullable|exists:factories,id',
            'item_number'           => ['required', 'min:1', 'max:64', $unique],
            'article_number'        => ['required', 'min:1', 'max:64'],
            'name'                  => 'required|min:1|max:200',
            'barcode'               => ['nullable','sometimes','max:32', $uniqueBarcode],
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
            'row_postage_method'    => 'nullable|sometimes',
            'custom_minimum_profit' => 'nullable|sometimes|numeric|min:0'
        ], [], [
            'supplier.id' => 'Supplier',
            'factories.*.id' => 'Factory'
        ]);

        $fields = [
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
            'unit_price_gbp',
            'custom_minimum_profit'
        ];

        if (!\Auth::user()->is_supplier) {
            $fields = array_merge($fields, [
                'is_excluded_from_commission',
                'uk_postage_method',
                'eu_postage_method',
                'row_postage_method'
            ]);
        }

        $product->fill($request->only($fields));
        if (\Auth::user()->hasRole(['supplier'])) {
            $product->supplier()->associate(\Auth::user()->supplier);
        } else {
            $product->supplier()->associate(Supplier::find($request->input('supplier.id')));
        }
        $product->factories()->sync(Factory::query()->findMany($request->input('factories.*.id')));
        $product->save();

        event(new ProductInformationChanged($product));

        return response()->json(fractal($product->fresh(), new ProductTransformer()));
    }

    /**
     * @param Request $request
     * @return \Spatie\Fractal\Fractal
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->authorize('create', Product::class);
        $unique = Rule::unique('products', 'item_number')
            ->whereNotNull('barcode')
            ->whereNull('deleted_at')
            ->where('barcode', $request->input('barcode'));
        $uniqueBarcode = Rule::unique('products', 'barcode')
            ->whereNull('deleted_at');

        $supplierRequired = \Auth::user()->hasRole(['supplier'])?'nullable|sometimes|':'required';

        $this->validate($request, [
            'supplier.id'           => $supplierRequired . '|exists:suppliers,id',
            'factory.id'            => 'nullable|exists:factories,id',
            'item_number'           => ['required', 'min:1', 'max:64', $unique],
            'article_number'        => 'required|min:1|max:64',
            'name'                  => 'required|min:1|max:200',
            'barcode'               => ['sometimes', 'max:32', $uniqueBarcode],
            'length_cm'             => 'sometimes|min:0|numeric',
            'width_cm'              => 'sometimes|min:0|numeric',
            'height_cm'             => 'sometimes|min:0|numeric',
            'case_weight_kg'        => 'sometimes|min:0|numeric',
            'unit_price'            => 'sometimes|min:0|numeric',
            'units_per_case'        => 'sometimes|min:0|numeric',
            'inner_qty'             => 'sometimes|min:0|numeric',
            'packing_instructions'  => 'sometimes|max:500',
            'product_description'   => 'sometimes|max:500',
            'uk_postage_method'     => 'nullable|sometimes',
            'eu_postage_method'     => 'nullable|sometimes',
            'row_postage_method'    => 'nullable|sometimes',
            'custom_minimum_profit' => 'nullable|sometimes|numeric|min:0'
        ], [
            'item_number.unique' => 'The item number/barcode combination has already been taken'
        ], [
            'supplier.id' => 'Supplier',
            'factory.id' => 'Factory'
        ]);
        $fields = [
            'item_number',
            'article_number',
            'name',
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
            'custom_minimum_profit'
        ];
        if (!\Auth::user()->is_supplier) {
            $fields = array_merge($fields, [
                'is_excluded_from_commission',
                'uk_postage_method',
                'eu_postage_method',
                'row_postage_method'
            ]);
        }
        $product = new Product($request->only($fields));
        if (\Auth::user()->hasRole(['supplier'])) {
            $product->supplier()->associate(\Auth::user()->supplier);
        } else {
            $product->supplier()->associate(Supplier::find($request->input('supplier.id')));
        }
        $product->save(); // save product first, then attach to factories join table
        $product->factories()->sync(Factory::query()->findMany($request->input('factories.*.id')));
        return fractal($product->fresh(), new ProductTransformer());
    }

    /**
     * @param Request $request
     * @param ProductRepository $productRepository
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, ProductRepository $productRepository)
    {
        $this->validate($request, [
            'sales_order_id' => 'nullable|sometimes|exists:sales_orders,id'
        ]);

        $paginator = $productRepository;

        // Restrict the catalogue of products that goes back to suppliers
        if (\Auth::user() && \Auth::user()->is_supplier) {
            $paginator = $paginator
                ->where('supplier_id', \Auth::user()->supplier_id)
                ->whereHas('factories', function($query) {
                    $query->whereIn('id', \Auth::user()->factories->pluck('id'));
                });
        }


         if($request->input('search_factory_id')) {
            $paginator = $paginator->whereHas('factories', function($query) use ($request) {
                $query->where('factories.id', $request->input('search_factory_id'));
            });
        }

        if ($request->input('sales_order_id')) {
            $salesOrder = SalesOrder::query()->findOrFail($request->input('sales_order_id'));
            if ($salesOrder->factory) {
                $paginator = $paginator->whereHas('factories', function($query) use ($salesOrder) {
                    return $query->where('id', $salesOrder->factory->id);
                });
            }
        }

        if($request->input('export')) {
            $paginator = $paginator
                ->whereNotNull('item_number')
                ->where('item_number', '!=', '');
            set_time_limit(120);
            ini_set('memory_limit', '256M');
            $export = new ProductExport([]);

            if ($request->input('product_ids')) {
                $productIds = explode(',', $request->input('product_ids'));
                if (count(array_filter($productIds)) > 0) {
                    $paginator->whereIn('id', array_filter($productIds));
                }
            }

            if($request->input('format') === 'simplified_china') {
                $export->setSimplifiedChinaExport();
            }

            if($request->input('format') === 'full_with_stock_data') {
                $export->setStockLevelRequired();
            }

            if($request->input('start_date')) {{
                $export->setPeriod($request->input('start_date'));
            }} else {
                $export->setPeriod(Carbon::now());
            }


            $paginator->chunk(150, function ($products) use ($export) {
                $export->setProducts($products);
                $export->getStock();
                $export->buildExport();
            });
            $export->setProducts([]);
            return $export->getCsvDownloadResponse();
        }

        $paginator = $paginator->paginate(
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

    public function copy(Request $request, Product $product)
    {
        $this->authorize('copy', $product);
        $newProduct = $product->replicate(['sorted_article_number']);
        $newProduct->name = "Copy of {$product->name}";
        $newProduct->save();

        return response()->json(fractal($newProduct, new ProductTransformer()));
    }
}
