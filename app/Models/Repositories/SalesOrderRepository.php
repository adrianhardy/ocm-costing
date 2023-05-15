<?php


namespace App\Models\Repositories;


use App\Models\SalesOrder;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class SalesOrderRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'reference' => 'LIKE',
        'supplier.name' => 'LIKE',
        'supplier_reference' => 'LIKE',
        'lines.product.item_number' => 'LIKE',
        'lines.product.primary_sku' => 'LIKE'
    ];
    public function model()
    {
        return SalesOrder::class;
    }

    public function boot()
    {
        $this->pushCriteria(app()->make(RequestCriteria::class));
    }
}
