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
            'product.primary_sku' => 'like',
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