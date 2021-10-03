<?php


namespace App\Models\Repositories;


use App\Models\Factory;
use App\Models\ProductPricing;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class ProductPricingRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'id' => 'like',
        'created_at' => 'like '

    ];
    public function model()
    {
        return ProductPricing::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
