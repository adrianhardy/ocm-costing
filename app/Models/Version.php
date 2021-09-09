<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class Version
 * @package App\Models
 *
 * @property array $model_data
 */
class Version extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'model_data'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'model_data' => 'json'
    ];

    /**
     * Sets up the relation
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function versionable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
