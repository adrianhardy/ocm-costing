<?php

namespace App\Transformers;

use App\Models\DailyPricing;
use League\Fractal\TransformerAbstract;

class DailyPricingTransformer extends TransformerAbstract
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
        'approver', 'country', 'product', 'submitter', 'pricing_history'
    ];

    public function includePricingHistory(DailyPricing $pricing){
        if(!$pricing->pricing_history) {
            return $this->null();
        }
        return $this->collection($pricing->pricing_history, new DailyPricingTransformer());
    }

    public function includeApprover(DailyPricing $pricing){
        if($pricing->approver) {
            return $this->item($pricing->approver, new UserTransformer());
        }
        return $this->null();
    }


    public function includeCountry(DailyPricing $pricing) {
        return $this->item($pricing->country, new CountryTransformer());
    }

    public function includeProduct(DailyPricing $pricing)
    {
        return $this->item($pricing->product, new ProductTransformer());
    }


    public function includeSubmitter(DailyPricing $pricing)
    {
        return $this->item($pricing->submitter, new UserTransformer);
    }

    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(DailyPricing $pricing )
    {
        return $pricing->only([
            'id',
            'submitted_at',
            'suggested_price',
            'fba_fee',
            'approved_at',
            'is_viable',
            'margin',
            'profit',
            'last_checked_at',
            'last_approved_at',
            'last_est_landed_cost'
        ]);
    }
}
