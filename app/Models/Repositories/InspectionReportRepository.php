<?php


namespace App\Models\Repositories;


use App\Models\Factory;
use App\Models\InspectionReport;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class InspectionReportRepository extends BaseRepository
{
    protected $fieldSearchable = [

    ];
    public function model()
    {
        return InspectionReport::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}