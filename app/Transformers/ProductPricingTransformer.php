<?php

namespace App\Transformers;

use App\Models\ProductPricing;
use League\Fractal\TransformerAbstract;

class ProductPricingTransformer extends TransformerAbstract
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
        'product', 'approver'
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(ProductPricing $pricing)
    {
        return $pricing->only([
            'id',
            'created_at',
            'updated_at',
            'est_landed_cost_per_unit_gbp',
            'marketplace_commission_pc',
            'suggested_unit_price_gbp',
            'unit_shipping_gbp',
            'unit_price_gbp',
            'gross_unit_cost_gbp',
            'margin_pc',
            'approved_at',
            'marketplace_fee',
            'profit'
        ]);
    }

    public function includeApprover(ProductPricing $productPricing)
    {
        if (!$productPricing->approver_id) {
            return $this->null();
        }
        return $this->item($productPricing->approver, new UserTransformer());
    }

    public function includeProduct(ProductPricing $pricing){
        if(!$pricing->product_id){
            return $this->null();
        }
        $transformer = new ProductTransformer();
        return $this->item($pricing->product, $transformer);
    }
}
