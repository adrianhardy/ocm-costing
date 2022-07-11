<?php

namespace App\Observers;

use App\Models\Country;
use App\Models\Currency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CurrencyObserver
{
    public function created(Currency $currency)
    {

        /**
         * get a list of countries from storage, if they have the same currency code sa the currency being created,
         * then we find our version of that country, and add associate the new currency to it
         */

        $json = json_decode(Storage::get('countries.json'));

        DB::beginTransaction();

        foreach($json->countries->country as $country) {
            if($country->currencyCode === $currency->code) {
                /**
                 * @var Country $dataCountry
                 */
                $dataCountry = Country::query()->where('iso_2_code', $country->countryCode)->first();
                $dataCountry->currency()->associate($currency);
                $dataCountry->save();
            }
        }

        DB::commit();
    }

}
