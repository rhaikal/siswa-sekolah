<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MapelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $nama = ['Pemrograman Web dan Perangkat Bergerak', 'Pemrograman Berbasis Object', 'Basis Data', 'Produk Kreatif dan Kewirausahaan'];
        $slug = ['PWDPB', 'PBO', 'BD', 'PKDK'];
        // $nama_guru = ['Pak Suwondo', 'Pak Fatony', 'Bu Ida', 'Bu Muna'];
        
        for ($i=0; $i < count($nama); $i++) { 
            $guru = [];
            for ($j=0; $j < rand(1, 3); $j++) { 
                $guru[] = fake()->name();
            }

            DB::collection('mapel')->insert([
                'nama' => $nama[$i], 
                'slug' => $slug[$i],
                'guru' => $guru,
            ]);
        }
    }
}
