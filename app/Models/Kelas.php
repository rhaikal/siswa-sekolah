<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    public function detailSiswa()
    {
        return $this->hasMany(Siswa::class, 'kelas_id', '_id');
    }
}
