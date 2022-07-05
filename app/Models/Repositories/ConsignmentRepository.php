<?php


namespace App\Models\Repositories;


use App\Models\Consignment;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class ConsignmentRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'reference' => 'like',
        'vessel_name' => 'like',
        'bl_number' => 'like'
    ];
    public function model()
    {
        return Consignment::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
