<?php

namespace App\Transformers;

use App\Models\Country;
use League\Fractal\TransformerAbstract;

class CountryTransformer extends TransformerAbstract
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
        'currency'
    ];

    public function includeCurrency(Country $country)
    {
        // never had to do this before, but returning $this->null()
        // kept translating as an empty array [] by the time it got
        // to the client. This enforces that 'currency' will be null
        if ($country->currency_id < 1) {
            return $this->primitive(null);
        }
        return $this->item($country->currency, new CurrencyTransformer());
    }

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Country $country)
    {
        return $country->only([
            'id', 'iso_2_code', 'name', 'has_pricing'
        ]);
    }
}
