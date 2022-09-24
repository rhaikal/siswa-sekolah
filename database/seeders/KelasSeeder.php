<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $jurusan = ['RPL', 'TKJ', 'SIJA', 'ANI'];
        $fasilitas = ['Komputer', 'AC', 'Kipas Angin', 'Proyektor'];

        for ($i=0; $i < 5; $i++) { 
            $fasilitas_kelas = Arr::random($fasilitas, rand(1, 4));
            $semester_awal = rand(2000, 2020);
            DB::collection('kelas')->insert([
                'jurusan' => $jurusan[rand(0,3)],
                'tahun_ajar' => [
                    'semester_awal' => $semester_awal, 
                    'semester_akhir' => ($semester_awal+3)
                ],
                'ruang' => [
                    'lokasi' => 'Ruang ' . rand(1, 20), 
                    'fasilitas' => $fasilitas_kelas
                ],
                'walikelas' => fake()->name(),
                'kapasitas' => 0,
            ]);
        }
    }
}
