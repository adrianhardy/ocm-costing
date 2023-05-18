<?php

namespace App\Transformers;

use App\Models\Product;
use League\Fractal\TransformerAbstract;
use Spatie\MediaLibrary\Models\Media;

class ProductTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
        //
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        'quotes', 'media', 'supplier', 'comments', 'comments.author', 'factories',
        'current_landed_cost_per_unit_gbp'
    ];

    public function includeCurrentLandedCostPerUnitGbp(Product $product)
    {
        return $this->primitive($product->current_landed_cost_per_unit_gbp);
    }

    public function includeQuoteLines(Product $product)
    {
        return $this->collection($product->quote_lines_product, new QuoteLineTransformer);
    }

    public function includeSupplier(Product $product)
    {
        return $this->item($product->supplier, new SupplierTransformer);
    }

    public function includeFactories(Product $product)
    {
        return $this->collection($product->factories, new FactoryTransformer());
    }

    public function includeComments(Product $product)
    {
        return $this->collection($product->comments, new CommentTransformer);
    }

    /**
     * @param Product $product
     * @return \League\Fractal\Resource\Collection
     */
    public function includeMedia(Product $product)
    {
        return $this->collection($product->getMedia('default'), function (Media $media) {
            return [
                'id' => $media->id,
                'url' => $media->getFullUrl()
            ];
        });
    }

    /**
     * /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Product $product)
    {
        return $product->only([
            'id',
            'item_number',
            'article_number',
            'name',
            'packing_instructions',
            'barcode',
            'length_m',
            'width_m',
            'height_m',
            'unit_length_m',
            'unit_width_m',
            'unit_height_m',
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
            'case_cubic_m',
            'case_cubic_ft',
            'unit_cubic_m',
            'unit_cubic_ft',
            'est_landed_cost_gbp',
            'is_excluded_from_commission',
            'primary_asin',
            'primary_sku',
            'additional_asins',
            'additional_skus',
            'uk_postage_method',
            'eu_postage_method',
            'row_postage_method',
            'pricing_notes',
            'unit_price_gbp',
            'has_duplicate_barcode',
            'has_duplicate_item_number',
            'custom_minimum_profit',
            'final_cost_gbp' // choose from either unit_price_gbp or est_landed_cost_gbp
        ]);
    }
}
