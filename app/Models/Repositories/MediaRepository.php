<?php


namespace App\Models\Repositories;


use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Spatie\MediaLibrary\Models\Media;

class MediaRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name' => 'like'
    ];


    public function model()
    {
        return Media::class;
    }

    public function boot()
    {
        $this->pushCriteria(app()->make(RequestCriteria::class));
    }
}
