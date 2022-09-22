<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Nilai extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'nilai';

    public function mapel()
    {
        return $this->belongsTo(Mapel::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
