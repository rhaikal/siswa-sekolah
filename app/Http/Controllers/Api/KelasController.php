<?php

namespace App\Http\Controllers\Api;

use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class KelasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $kelas = Kelas::all();

        $list = [];
        foreach ($kelas as $kls) {
            $list[] = [
                '_id' => $kls->_id,
                'jurusan' => $kls->jurusan,
                'tahun_ajar' => $kls->tahun_ajar,
                'kapasitas' => $kls->kapasitas
            ];
        }

        return response()->json($list);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jurusan' => 'required|string|min:0|max:3',
            'semester_awal' => 'integer',
            'semester_akhir' => 'integer' . (isset($request->semester_awal) ? ('|min:' . $request->semester_awal) : ''),
            'lokasi_ruang' => 'string',
            'fasilitas_ruang' => [Rule::excludeIf(!isset($request->lokasi_ruang)), 'string', 'regex:/\w$/'],
            'walikelas' => 'string',
        ]);
        
        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Gagal Menambahkan Data Kelas',
                'errors' => $validator->errors()
            ]);
        }

        $validatedData = $validator->validated();
        
        $data = [];

        // data jurusan
        // $jurusan = ['RPL', 'TKJ', 'SIJA', 'ANI'];
        $data['jurusan'] = $validatedData['jurusan'];
        
        // data tahun_ajar
        if(!isset($validatedData['semester_awal'])){
            $data['tahun_ajar']['semester_awal'] = Carbon::now()->year;
        } else {
            $data['tahun_ajar']['semester_awal'] = $validatedData['semester_awal'];
        }
        
        if(!isset($validatedData['semester_akhir'])){
            $data['tahun_ajar']['semester_akhir'] = Carbon::now()->year + 3;
        } else {
            $data['tahun_ajar']['semester_akhir'] = $validatedData['semester_akhir'];
        }
        
        // data ruang
        if(isset($validatedData['lokasi_ruang'])){
            $data['ruang']['lokasi'] = $validatedData['lokasi_ruang'];
        }

        if(isset($validatedData['fasilitas_ruang'])){
            if(strpos($validatedData['fasilitas_ruang'], ',') !== false){
                $validatedData['fasilitas_ruang'] = preg_replace('/,\s+/', ',',$validatedData['fasilitas_ruang']);
                $validatedData['fasilitas_ruang'] = explode(',', $validatedData['fasilitas_ruang']);
            } else {
                $validatedData['fasilitas_ruang'] = [$validatedData['fasilitas_ruang']];
            }
            $data['ruang']['fasilitas'] = $validatedData['fasilitas_ruang'];
        }

        // data walikelas
        if(isset($validatedData['walikelas'])){
            $data['walikelas'] = $validatedData['walikelas'];
        }
        
        $data['kapasitas'] = 0;

        $kelas = Kelas::create($data);

        return response()->json($kelas);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Kelas  $kelas
     * @return \Illuminate\Http\Response
     */
    public function show(Kelas $kelas)
    {
        return response()->json($kelas);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Kelas  $kelas
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Kelas $kelas)
    {
        $validator = Validator::make($request->all(), [
            'jurusan' => 'string',
            'semester_awal' => 'integer',
            'semester_akhir' => 'integer|min:' . (isset($request->semester_awal) ? ($request->semester_awal + 1) : ($kelas->tahun_ajar['semester_awal'] + 1)),
            'lokasi_ruang' => 'string',
            'fasilitas_ruang' => [Rule::excludeIf(!isset($kelas->ruang['lokasi'])), 'string', 'regex:/\w$/'],
            'walikelas' => 'string',
        ]);

        $validatedData = $validator->validated();

        $data = [
            'tahun_ajar' => [
                'semester_awal' => $kelas->tahun_ajar['semester_awal'],
                'semester_akhir' => $kelas->tahun_ajar['semester_akhir']
            ],
            'ruang' => [
                'lokasi' => $kelas->ruang['lokasi'],
                'fasilitas' => $kelas->ruang['fasilitas'],
            ]
        ];

        // data jurusan
        if(isset($validatedData['jurusan'])){
            $data['jurusan'] = $validatedData['jurusan'];
        }

        // data tahun_ajar
        if(isset($validatedData['semester_awal'])){
            $data['tahun_ajar']['semester_awal'] = $validatedData['semester_awal'];
        }

        if(isset($validatedData['semester_akhir'])){
            $data['tahun_ajar']['semester_akhir'] = $validatedData['semester_akhir'];
        }

        // data ruang
        if(isset($validatedData['lokasi_ruang'])){
            $data['ruang']['lokasi'] = $validatedData['lokasi_ruang'];
        }

        if(isset($validatedData['fasilitas_ruang'])){
            if(strpos(',', $validatedData['fasilitas_ruang']) !== false){
                $validatedData['fasilitas_ruang'] = preg_replace('/,\s+/', ',',$validatedData['fasilitas_ruang']);
                $validatedData['fasilitas_ruang'] = explode(',', $validatedData['fasilitas_ruang']);
            } else {
                $validatedData['fasilitas_ruang'] = [$validatedData['fasilitas_ruang']];
            }

            $data['ruang']['fasilitas'] = $validatedData['fasilitas_ruang'];
        }
        
        // data walikelas
        if(isset($validatedData['walikelas'])){
            $data['walikelas'] = $validatedData['walikelas'];
        }

        $kelas->update($data);

        return response()->json($kelas);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Kelas  $kelas
     * @return \Illuminate\Http\Response
     */
    public function destroy(Kelas $kelas)
    {
        $kelas->delete();

        return response()->json([
            'status' => true,
            'message' => 'Berhasil Menghapus Data',
        ]);
    }
}
