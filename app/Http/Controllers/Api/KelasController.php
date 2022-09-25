<?php

namespace App\Http\Controllers\Api;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;

use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\KelasResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class KelasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $kelas = Kelas::paginate(2);

        return KelasResource::collection($kelas);
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
            'jurusan' => 'required|string',
            'semester_awal' => 'integer',
            'semester_akhir' => 'integer' . (isset($request->semester_awal) && is_int($request->semester_awal) ? ('|min:' . $request->semester_awal) : ''),
            'lokasi_ruang' => 'string',
            'fasilitas_ruang' => [Rule::excludeIf(!isset($request->lokasi_ruang)), 'string', 'regex:/\w$/'],
            'walikelas' => 'string',
        ]);
        
        if($validator->fails()){
            return new ErrorResource($validator->errors(), 'gagal menambahkan collection kelas');
        }

        $validatedData = $validator->validated();
        
        $data = $this->olahKelas($validatedData);

        $kelas = Kelas::create($data);

        return new KelasResource($kelas, 'berhasil menambahkan collection kelas baru');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Kelas  $kelas
     * @return \Illuminate\Http\Response
     */
    public function show(Kelas $kelas)
    {
        return new KelasResource($kelas, 'berhasil mengambil collection kelas');
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
            'semester_akhir' => 'integer|min:' . (isset($request->semester_awal) && is_int($request->semester_awal) ? ($request->semester_awal + 1) : ($kelas->tahun_ajar['semester_awal'] + 1)),
            'lokasi_ruang' => 'string',
            'fasilitas_ruang' => [Rule::excludeIf(!isset($kelas->ruang['lokasi'])), 'string', 'regex:/\w$/'],
            'walikelas' => 'string',
        ]);

        if($validator->fails()){
            return new ErrorResource($validator->errors(), 'gagal mengubah collection kelas');
        }

        $validatedData = $validator->validated();

        $data = $this->olahKelas($validatedData, $kelas); 
        
        $kelas->update($data);

        return new KelasResource($kelas, 'berhasil mengubah collection kelas');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Kelas  $kelas
     * @return \Illuminate\Http\Response
     */
    public function destroy(Kelas $kelas)
    {
        if(isset($kelas->siswa)){
            $this->removeAllSiswa($kelas);
        }

        $kelas->delete();

        return response()->json([
            'status' => true,
            'message' => 'berhasil menghapus collection kelas',
        ]);
    }

    private function olahKelas($processedData, Kelas $kelas = null){
        // data jurusan
        // $jurusan = ['RPL', 'TKJ', 'SIJA', 'ANI'];
        if (isset($processedData['jurusan'])) {            
            $data['jurusan'] = $processedData['jurusan'];
        }
        
        // data tahun_ajar
        if(isset($processedData['semester_awal'])){
            $data['tahun_ajar']['semester_awal'] = $processedData['semester_awal'];
        } elseif ($kelas != null) {
            $data['tahun_ajar']['semester_awal'] = $kelas->tahun_ajar['semester_awal'];
        } else {
            $data['tahun_ajar']['semester_awal'] = Carbon::now()->year;
        }
        
        if(isset($processedData['semester_akhir'])){
            $data['tahun_ajar']['semester_akhir'] = $processedData['semester_akhir'];
        } elseif($kelas != null) {
            $data['tahun_ajar']['semester_akhir'] = $kelas->tahun_ajar['semester_akhir'];
        } else {
            $data['tahun_ajar']['semester_akhir'] = $data['tahun_ajar']['semester_awal'] + 3;
        }
        
        // data ruang
        if(isset($processedData['lokasi_ruang'])){
            $data['ruang']['lokasi'] = $processedData['lokasi_ruang'];
        } elseif ($kelas != null) {
            $data['ruang']['lokasi'] = $kelas->ruang['lokasi'];
            $kelas->unset('ruang.fasilitas');
        }

        if(isset($processedData['fasilitas_ruang'])){
            if(strpos($processedData['fasilitas_ruang'], ',') !== false){
                $processedData['fasilitas_ruang'] = preg_replace('/,\s+/', ',',$processedData['fasilitas_ruang']);
                $processedData['fasilitas_ruang'] = explode(',', $processedData['fasilitas_ruang']);
            } else {
                $processedData['fasilitas_ruang'] = [$processedData['fasilitas_ruang']];
            }
            $data['ruang']['fasilitas'] = $processedData['fasilitas_ruang'];
        } elseif ($kelas != null && isset($processedData['lokasi_ruang'])  == false) {
            $data['ruang']['fasilitas'] = $kelas->ruang['fasilitas'];
        }

        // data walikelas
        if(isset($processedData['walikelas'])){
            $data['walikelas'] = $processedData['walikelas'];
        }
        
        if(isset($kelas->siswa) == false){
            $data['kapasitas'] = 0;        
        }

        return $data;
    }

    public function addSiswa(Kelas $kelas, Siswa $siswa)
    {
        $siswaController = new SiswaController;
        
        $data['siswa_id'] = $siswa->_id;
        $errorMsg = ['siswa_id.unique' => 'the siswa already inputed'];

        $validator = Validator::make($data, [
            'siswa_id' => 'unique:kelas,siswa.siswa_id'
        ], $errorMsg);

        if($validator->fails()){
            return new ErrorResource($validator->errors(), 'gagal menambahkan siswa');
        }
        
        if(isset($siswa->kelas) == true){
            $siswaController->removeKelas($siswa);
        }

        $siswaController->addKelas($siswa, $kelas);
        
        $siswa->update([
            'kelas_id' => $kelas->_id,
            'kelas' => $siswaController->olahKelas($kelas)
        ]);

        return new KelasResource($kelas, 'berhasil menambahkan siswa');
    }

    public function removeAllSiswa(Kelas $kelas)
    {
        if(isset($kelas->detailSiswa) == true){
            foreach ($kelas->detailSiswa as $siswa) {
                $siswa->unset('kelas_id');
                $siswa->unset('kelas');
            }
    
            $kelas->unset('siswa');
            $kelas->update(['kapasitas' => 0]);
    
            return new KelasResource($kelas, 'berhasil mengeluarkan semua siswa');
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'tidak ada siswa di dalam kelas ini'
            ]);
        }
    }

    public function removeSiswa(Kelas $kelas, Siswa $siswa)
    {
        $kelas->decrement('kapasitas');
        
        if($kelas->kapasitas == 0){
            $kelas->unset('siswa');
        } else {
            $siswa_id = $siswa->_id;
            $siswa_nama = $siswa->nama;
            $kelas->pull('siswa', [
                    'siswa_id' => $siswa_id,
                    'nama' => $siswa_nama,
            ]);
            
            // fix bug dari jessengers/laravel-mongodb, tidak mengupdate model walaupun ditabase berubah
            $kelas = Kelas::find($kelas->_id);
        }
        
        $siswa->unset('kelas_id');
        $siswa->unset('kelas');

        return new KelasResource($kelas, 'berhasil mengeluarkan siswa');
    }
}
