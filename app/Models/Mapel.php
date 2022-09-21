<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Mapel extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'mapel';
}
