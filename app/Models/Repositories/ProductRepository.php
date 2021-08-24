<?php


namespace App\Models\Repositories;


use App\Models\Product;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class ProductRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name' => 'LIKE',
        'barcode' => 'LIKE',
        'item_number' => 'LIKE',
        'article_number' => 'LIKE'
    ];

    public function model()
    {
        return Product::class;
    }

    public function boot()
    {
        $this->pushCriteria(app()->make(RequestCriteria::class));
    }

}
