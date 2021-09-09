<?php


namespace App\Models\Repositories;


use App\Models\SalesOrderLine;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class SalesOrderLineRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'reference' => 'LIKE',
        'supplier.name' => 'LIKE'
    ];
    public function model()
    {
        return SalesOrderLine::class;
    }

    public function boot()
    {
        $this->pushCriteria(app()->make(RequestCriteria::class));
    }
}