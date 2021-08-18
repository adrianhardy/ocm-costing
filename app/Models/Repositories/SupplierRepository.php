<?php


namespace App\Models\Repositories;


use App\Models\Supplier;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class SupplierRepository extends BaseRepository
{
    public function model()
    {
        return Supplier::class;
    }

    public function boot()
    {
        $this->pushCriteria(app()->make(RequestCriteria::class));
    }

}
