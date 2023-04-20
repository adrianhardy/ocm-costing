<?php


namespace App\Models\Repositories;


use App\Models\DailyPricing;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class DailyPricingRepository extends BaseRepository
{
    public function getFieldsSearchable()
    {
        return [
            'product.item_number' => 'like',
            'product.name' => 'like',
            'product.primary_sku' => 'like',
            'product.primary_asin' => 'like',
        ];
    }

    public function model()
    {
        return DailyPricing::class;
    }

    public function boot()
    {
        $this->pushCriteria(app()->make(RequestCriteria::class));
    }

}