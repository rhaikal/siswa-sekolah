<?php

namespace Database\Seeders;

use App\Models\Mapel;
use App\Models\Nilai;
use App\Models\Siswa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class NilaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $mapels = Mapel::all();

        foreach ($mapels as $mapel) 
        {
            $siswas = Siswa::all();
            foreach ($siswas as $siswa) {
                $nilai_id = DB::collection('nilai')->insertGetId([
                    'mapel_id' => $mapel->_id,
                    'nama_mapel' => $mapel->nama,
                    'siswa_id' => $siswa->_id,
                    'nama_siswa' => $siswa->nama,
                ]);
                
                $semester_kelas = $siswa->kelas['semester'];
                for ($i=0; $i < $semester_kelas; $i++) {     
                    $nilai = Nilai::where('_id', '=', $nilai_id)->first();

                    $latihan_soal = [];
                    for ($j=0; $j < 4; $j++) { 
                        $latihan_soal[] = rand(80, 100);
                    }
                    
                    $ulangan_harian = [];
                    for ($j=0; $j < 2; $j++) { 
                        $ulangan_harian[] = rand(75,100);
                    }
                    $uts = rand(70,100);
                    $us = rand(70, 100);

                    $nilai->update([
                        'latihan_soal.' . 'semester_' . ($i + 1) => $latihan_soal,
                        'ulangan_harian.' . 'semester_' . ($i + 1) => $ulangan_harian,
                        'ulangan_tengah_semester.' . 'semester_' . ($i + 1) => $uts,
                        'ulangan_semester.' . 'semester_' . ($i + 1) => $us
                    ]);
                    
                    $nilai_akhir = 
                    (array_sum($latihan_soal)/4) * 0.15 + 
                    (array_sum($ulangan_harian)/2) * 0.20 +
                    $uts * 0.25 +
                    $us * 0.40;

                    $siswa->update(['penilaian.' . 'semester_' . ($i + 1) . '.' .  strtolower($mapel->slug) => [
                        'mapel' => $mapel->nama,
                        'nilai' => $nilai_akhir
                        ]
                    ]);
                }
            }
        }
    }
}
