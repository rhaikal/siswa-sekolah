<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Mapel extends Model
{
    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'mongodb';
    
    /**
     * The collection associated with the model.
     *
     * @var string
     */
    protected $collection = 'mapel';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function nilai()
    {
        return $this->hasMany(Nilai::class, 'mapel_id', '_id');
    }
}
