<?php


namespace App\Models\Repositories;


use App\Models\SalesOrder;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class SalesOrderRepository extends BaseRepository
{
    public function model()
    {
        return SalesOrder::class;
    }

    public function boot()
    {
        $this->pushCriteria(app()->make(RequestCriteria::class));
    }
}
