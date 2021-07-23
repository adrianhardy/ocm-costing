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
        'quotes', 'media'
    ];

    public function includeQuoteLines(Product $product)
    {
        return $this->collection($product->quote_lines_product, new QuoteLineTransformer);
    }

    public function includeMedia(Product $product)
    {
        return $this->collection($product->getMedia('quote'), function(Media $media) {
            return [
                'id' => $media->id,
                'url' => $media->getFullUrl()
            ];
        });
    }

    /**
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Product $product)
    {
        return $product->only(['id', 'name', 'article_number', 'item_number']);
    }
}
