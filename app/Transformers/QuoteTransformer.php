<?php

namespace App\Transformers;

use App\Models\Quote;
use League\Fractal\TransformerAbstract;

class QuoteTransformer extends TransformerAbstract
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
        'pivot'
    ];

    public function includePivot(Quote $quote)
    {
        return $this->primitive($quote->pivot);
    }

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Quote $quote)
    {
        return $quote->only(['id', 'reference']);
    }
}
