<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\SiswaResource;
use Illuminate\Support\Facades\Validator;

class SiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $siswa = Siswa::paginate(10);

        return SiswaResource::collection($siswa);
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
            'nis' => 'string',
            'nama' => 'required|string',
            'kelas_id' => 'required|exists:App\Models\Kelas,_id',
            'jenis_kelamin' => 'required|in:laki-laki,perempuan',
            'agama' => 'required|string',
            'alamat' => 'required|string',
        ]);

        if($validator->fails()){
            return new ErrorResource($validator->errors(), 'gagal menambahkan collection siswa');
        }

        $validatedData = $validator->validated();

        if(!isset($validatedData['nis'])){
            $validatedData['nis'] = fake()->nik();
        }
        
        $kelas = Kelas::find($validatedData['kelas_id']);
        $validatedData['kelas'] = $this->olahKelas($kelas);

        $siswa = Siswa::create($validatedData);

        $this->addKelas($siswa, $kelas);

        return new SiswaResource($siswa, 'berhasil menambahkan collection siswa baru');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Siswa  $siswa
     * @return \Illuminate\Http\Response
     */
    public function show(Siswa $siswa)
    {
        return new SiswaResource($siswa, 'berhasil mengambil collection siswa');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Siswa  $siswa
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Siswa $siswa)
    {
        $validator = Validator::make($request->all(), [
            'nis' => 'string',
            'nama' => 'string',
            'kelas_id' => [Rule::excludeIf($siswa->_id == $request->kelas_id), 'exists:App\Models\Kelas,_id'],
            'jenis_kelamin' => 'in:laki-laki,perempuan',
            'agama' => 'string',
            'alamat' => 'string',
        ]);

        if($validator->fails()){
            return new ErrorResource($validator->errors(), 'gagal mengubah collection siswa');
        }

        $validatedData = $validator->validated();

        if(isset($validatedData['kelas_id'])){
            $kelas = Kelas::find($validatedData['kelas_id']);
        
            $validatedData['kelas'] = $this->olahKelas($kelas);

            if(isset($siswa->kelas)){
                $this->removeKelas($siswa);
            }
                
            $this->addKelas($siswa, $kelas);
        }
        
        $siswa->update($validatedData);

        return new SiswaResource($siswa, 'berhasil mengubah collection siswa');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Siswa  $siswa
     * @return \Illuminate\Http\Response
     */
    public function destroy(Siswa $siswa)
    {
        $this->removeKelas($siswa);

        $siswa->nilai()->whereIn('siswa_id', [$siswa->_id])->delete();

        $siswa->delete();

        return response()->json([
            'status' => true,
            'message' => 'berhasil menghapus collection siswa',
        ]);
    }

    public function olahKelas(Kelas $kelas)
    {
        $tahun = Carbon::now()->year;
        $semester_awal = $kelas->tahun_ajar['semester_awal'];
        if($semester_awal != $tahun){
            $semester = 3;
            if($semester_awal - $tahun == 1){
                $semester = 2;
            }
        } else {
            $semester = 1;
        }

        return [
            'semester' => $semester,
            'jurusan' => $kelas->jurusan
        ];
    }

    public function addKelas(Siswa $siswa, Kelas $kelas)
    {        
        $kelas->push('siswa', [["siswa_id" => $siswa->_id, 'nama' => $siswa->nama]]);
        $kelas->increment('kapasitas');
        
        return $siswa;
    }

    public function removeKelas(Siswa $siswa)
    {
        $siswa->detailKelas->decrement('kapasitas');
        if($siswa->detailKelas->kapasitas == 0){
            $siswa->detailKelas->unset('siswa');
        } else {
            $siswa_id = $siswa->_id;
            $siswa_nama = $siswa->nama; 
            $siswa->detailKelas->pull('siswa', [
                'siswa_id' => $siswa_id,
                'nama' => $siswa_nama,
            ]);
        }
        
        return $siswa;
    }
}
