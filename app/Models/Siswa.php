<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Siswa extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'siswa';

    public function detailKelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }
}
