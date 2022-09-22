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
                $siswa_id = fake()->uuid();
                $nama_siswa = fake()->name(($gender_siswa == 'laki-laki') ? 'male' : 'female');
                DB::collection('siswa')->insert([
                    '_id' => $siswa_id,
                    'nis' => fake()->nik(),
                    'nama' => $nama_siswa,
                    'kelas_id' => $kelas->_id,
                    'kelas' => [$tingkat_kelas, $jurusan_kelas],
                    'jenis_kelamin' => $gender_siswa,
                    'agama' => $agama[rand(0, 5)],
                    'alamat' => fake()->address(),
                    'foto' => fake()->imageUrl(360, 360, 'person', true, 'siswa', true, 'jpg')
                ]);
                
                DB::collection('kelas')->where('_id', $kelas->_id)->push('siswa', ["_id" => $siswa_id, 'nama' => $nama_siswa]);
            }
        }
    }
}