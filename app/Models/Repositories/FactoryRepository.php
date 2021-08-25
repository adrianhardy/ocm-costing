<?php


namespace App\Models\Repositories;


use App\Models\Factory;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class FactoryRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name' => 'like',
        'contact_name' => 'like',
        'country' => 'like',

    ];
    public function model()
    {
        return Factory::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
