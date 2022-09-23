<?php

namespace Database\Seeders;

use App\Models\Kelas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $jenis_kelamin = ['laki-laki', 'perempuan'];
        $agama = ['islam', 'kristen', 'buudha', 'hindu', 'konghucu', 'katolik'];
        
        for ($i=0; $i < 2; $i++) { 
            $kelas = Kelas::all()->random();
            $jurusan_kelas = $kelas->jurusan;
            $tingkat_kelas =  rand(10, 12);
            for ($j=0; $j < 40; $j++) { 
                $gender_siswa = $jenis_kelamin[rand(0, 1)];
                $nama_siswa = fake()->name(($gender_siswa == 'laki-laki') ? 'male' : 'female');
                
                $siswa_id = DB::collection('siswa')->insertGetId([
                    'nis' => fake()->nik(),
                    'nama' => $nama_siswa,
                    'kelas_id' => $kelas->_id,
                    'kelas' => [
                        'tingkat' => $tingkat_kelas, 
                        'jurusan' => $jurusan_kelas
                    ],
                    'jenis_kelamin' => $gender_siswa,
                    'agama' => $agama[rand(0, 5)],
                    'alamat' => fake()->address(),
                    'foto' => fake()->imageUrl(360, 360, 'person', true, 'siswa', true, 'jpg'),
                    'penilaian' => [
                        '10' => null,
                        '11' => null,
                        '12' => null
                    ]
                ]);
                $siswa_id = (String)$siswa_id;
                
                DB::collection('kelas')->where('_id', $kelas->_id)->push('siswa', ["siswa_id" => $siswa_id, 'nama' => $nama_siswa]);
            }
        }
    }
}