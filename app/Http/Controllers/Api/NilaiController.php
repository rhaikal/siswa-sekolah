<?php

namespace App\Http\Controllers\Api;

use App\Models\Mapel;
use App\Models\Nilai;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\NilaiResource;
use Illuminate\Support\Facades\Validator;

class NilaiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->hasAny(['mapel_id', 'siswa_id'])){
            $paginate = (isset($request->paginate) ? $request->paginate : 5);
            
            if($request->has(['mapel_id', 'siswa_id'])){
                $nilai = $nilai = Nilai::where('mapel_id', '=', $request->mapel_id)->where('siswa_id', '=', $request->siswa_id)->get();
            } elseif($request->has('mapel_id')) {
                $nilai = Nilai::where('mapel_id', '=', $request->mapel_id)->paginate($paginate);
            } elseif ($request->has('siswa_id')) {
                $nilai = Nilai::where('siswa_id', '=', $request->siswa_id)->paginate($paginate);
            }
            
            return NilaiResource::collection($nilai);
        } else {
            return NilaiResource::collection(Nilai::paginate(10));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function insert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mapel_id' => 'required|string|exists:App\Models\Mapel,_id',
            'siswa_id' => 'required|string|exists:App\Models\Siswa,_id',
            'semester' => 'integer|max:3',
            'mass_assignment' => Rule::in(['true', 'false']),
            'jenis_penilaian' => [Rule::requiredIf(isset($request->mass_assignment) == false), 'integer', 'min:1', 'max:4'],
            'nilai' => 'required|' . (isset($request->mass_assignment)) ? 'string|regex:/\d$/' : 'integer|max:100'
        ]);

        if($validator->fails()){
            return new ErrorResource($validator->errors(), 'gagal menambahkan nilai');
        }

        $validatedData = $validator->validated();

        $mapel = Mapel::find($validatedData['mapel_id']);
        $siswa = Siswa::find($validatedData['siswa_id']);

        if(isset($validatedData['semester']) == true && ($validatedData['semester'] > $siswa->kelas['semester'])){
            return new ErrorResource(['semester' => 'The field semester is bigger than siswa semester'], 'gagal menambahkan nilai');
        }

        $nilai = Nilai::where('mapel_id', '=', $mapel->_id)->where('siswa_id', '=', $siswa->_id)->first();
        if(isset($nilai) == false){
            $data = $this->olahNilai($mapel, $siswa);
            $nilai = Nilai::create($data);
        }
        
        if(isset($validatedData['semester']) == true){
            $semester = $validatedData['semester'];
        } else {
            $semester = $siswa->kelas['semester'];
        }
                
        if(isset($validatedData['mass_assignment']) == true && $validatedData['mass_assignment'] == 'true'){
            if(strpos($validatedData['nilai'], ',') !== false){
                $validatedData['nilai'] = preg_replace('/,\s+/', ',',$validatedData['nilai']);
                $validatedData['nilai'] = explode(',', $validatedData['nilai']);
            } else {
                if(isset($validatedData['jenis_penilaian']) && $validatedData['jenis_penilaian'] > 2){
                    $validatedData['nilai'] = $validatedData['nilai'];
                } else {
                    $validatedData['nilai'] = [$validatedData['nilai']];
                }
            }

            $validatedData['mass_assignment'] = true;
        } else {
            $validatedData['mass_assignment'] = false;
        }

        if(isset($validatedData['jenis_penilaian'])){
            $count = $this->countNilai($nilai, $semester);
            
            $error_msg = 'The nilai field already have ';
            if($validatedData['jenis_penilaian'] == 1){
                if($validatedData['mass_assignment'] == true){
                    $count['ls'] += count($validatedData['nilai']);
                }

                $error_msg .= $count['ls'] . ' values';
                if($count['ls'] > 4){
                    return new ErrorResource(['nilai' => $error_msg], 'hanya bisa memasukkan 4 nilai latihan soal dalam 1 semester');
                }
                
                $jenis = 'latihan_soal';
            } 
    
            if($validatedData['jenis_penilaian'] == 2){
                if($validatedData['mass_assignment'] == true){
                    $count['uh'] += count($validatedData['nilai']);
                }

                $error_msg .= $count['uh'] . ' values';
                if($count['uh'] > 2){
                    return new ErrorResource(['nilai' => $error_msg], 'hanya bisa memasukan 2 nilai ulangan harian dalam 1 semester');
                }
                
                $jenis = 'ulangan_harian';
            }
            
            if($validatedData['jenis_penilaian'] == 3){
                $error_msg .= $count['uts'] . ' value';
                if($count['uts'] == 1){
                    return new ErrorResource(['nilai' => $error_msg], 'hanya bisa memasukan 1 nilai ulangan tengah semester dalam 1 semester');
                }
                
                $jenis = 'ulangan_tengah_semester';
            }
            
            if($validatedData['jenis_penilaian'] == 4){
                $error_msg .= $count['us'] . ' value';
                if($count['us'] == 1){
                    return new ErrorResource(['nilai' => $error_msg], 'hanya bisa memasukan 1 nilai ulangan semester dalam 1 semester');
                }
    
                $jenis = 'ulangan_semester';
            } 
        } else {
            $msg['nilai'] = 'The nilai field must have 8 values';
            if(count($validatedData['nilai']) > 8) {
                return new ErrorResource($msg, 'hanya bisa memasukan maksimal total 8 nilai');
            }
            
            if(count($validatedData['nilai']) < 8) {
                return new ErrorResource($msg, 'hanya bias memasukan minimal total 8 nilai');
            }
        }

        if($validatedData['mass_assignment'] == true && isset($validatedData['jenis_penilaian'])  == false){
            $nilai_skor = [];
            $nilai_semester = 'semester_';
            if(isset($validatedData['semester']) == true){
                $nilai_semester .= $validatedData['semester'];
            } else {
                $nilai_semester .= $semester;
            }

            for ($i=0; $i < count($validatedData['nilai']); $i++) { 
                if($i < 4) {
                    $nilai_skor['latihan_soal.' . $nilai_semester][] = $validatedData['nilai'][$i];
                } elseif ($i < 6) {
                    $nilai_skor['ulangan_harian.' . $nilai_semester][] = $validatedData['nilai'][$i];
                } elseif ($i < 7) {
                    $nilai_skor['ulangan_tengah_semester.' . $nilai_semester] = $validatedData['nilai'][$i];
                } elseif ($i < 8) {
                    $nilai_skor['ulangan_semester.' . $nilai_semester] = $validatedData['nilai'][$i];
                }
            }

            Nilai::where('_id', '=', $nilai->_id)->update($nilai_skor);
        } else {
            if(is_array($validatedData['nilai']) && $validatedData['jenis_penilaian'] <= 2){
                $validatedData['nilai'] = array_map('intval', $validatedData['nilai']);
            } else {
                $validatedData['nilai'] = (int)$validatedData['nilai'];
            }
            
            if($validatedData['jenis_penilaian'] <= 2){
                $nilai->push($jenis . '.semester_' . $semester, $validatedData['nilai']); 
            } else {
                Nilai::where('_id', '=', $nilai->_id)->update(array($jenis . '.semester_' . $semester => $validatedData['nilai']));
            }
            
        }
        $nilai = Nilai::find($nilai->_id);
    
        $count = $this->countNilai($nilai, $semester);
        if($count['ls'] + $count['uh'] + $count['uts'] + $count['us'] == 8){
            $this->updateRataRata($mapel ,$nilai, $semester);
        }

        return new NilaiResource($nilai, 'nilai berhasil dimasukkan');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Nilai  $nilai
     * @return \Illuminate\Http\Response
     */
    public function show(Nilai $nilai)
    {
        return new NilaiResource($nilai, 'berhasil mengambil collection nilai');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Nilai  $nilai
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nilai_id' => [Rule::requiredIf($request->has(['mapel_id', 'siswa_id']) == false), 'exists:nilai,_id'],
            'siswa_id' => [Rule::requiredIf($request->has('mapel_id')), Rule::excludeIf($request->has('nilai_id')), 'exists:siswa,_id'],
            'mapel_id' => [Rule::requiredIf($request->has('siswa_id')), Rule::excludeIf($request->has('nilai_id')), 'exists:mapel,_id'],
        ]);

        if($validator->fails()){
            return new ErrorResource($validator->errors(), 'gagal menghapus kelas');
        }

        $validatedData = $validator->validated();

        if(isset($validatedData['siswa_id']) && isset($validatedData['mapel_id'])){
            $nilai = Nilai::where('siswa_id', '=', $validatedData['siswa_id'])->where('mapel_id', '=', $validatedData['mapel_id'])->first();
        } else {
            $nilai = Nilai::find($validatedData['nilai_id']);
        }

        $mapel = $nilai->mapel;
        $siswa = $nilai->siswa;
    
        for ($i=0; $i < $siswa->kelas['semester']; $i++) {
            if(isset($siswa->penilaian) && count($siswa->penilaian) <= 1){
                $siswa->unset('penilaian');
            }elseif(isset($siswa->penilaian['semester_'  . $i + 1]) == true && count($siswa->penilaian['semester_'  . $i + 1]) <= 1){
                $siswa->unset('penilaian.' . 'semester_' . $i + 1);
            } else {
                $siswa->unset('penilaian.'  . 'semester_' . $i + 1 . '.' . strtolower($mapel->slug));
            }            
        }

        $nilai->delete();

        return response()->json([
            'status' => true,
            'message' => 'berhasil menghapus collection kelas',
        ]);
    }

    public function olahNilai(Mapel $mapel = null, Siswa $siswa = null)
    {
        if(isset($mapel)){
            $data['mapel_id'] = $mapel->_id;
            $data['nama_mapel'] = $mapel->nama;
        }

        if (isset($siswa)) {
            $data['siswa_id'] = $siswa->_id;
            $data['nama_siswa'] = $siswa->nama;
        }

        return $data;
    }

    public function updateRataRata(Mapel $mapel, Nilai $nilai, $semester)
    {
        $nilai_akhir = $nilai_akhir = 
        (array_sum($nilai->latihan_soal['semester_' . $semester])/4) * 0.15 + 
        (array_sum($nilai->ulangan_harian['semester_' . $semester])/2) * 0.20 +
        ($nilai->ulangan_tengah_semester['semester_' . $semester]) * 0.25 +
        ($nilai->ulangan_semester['semester_' . $semester]) * 0.40;

        Siswa::where("_id", "=", $nilai->siswa_id)->update(['penilaian.' . 'semester_' . ($semester) . '.' .  strtolower($mapel->slug) => [
            'mapel' => $mapel->nama,
            'nilai' => $nilai_akhir
            ]
        ]);
    }

    public function countNilai(Nilai $nilai, $semester){
        $count = [];

        if(isset($nilai->latihan_soal['semester_' . $semester])){
            $count['ls'] = count($nilai->latihan_soal['semester_' . $semester]);
        } else {
            $count['ls'] = 0;
        }

        if(isset($nilai->ulangan_harian['semester_' . $semester])){
            $count['uh'] = count($nilai->ulangan_harian['semester_' . $semester]);
        } else {
            $count['uh'] = 0;
        }

        if(isset($nilai->ulangan_tengah_semester['semester_' . $semester])){
            $count['uts'] = 1;
        } else {
            $count['uts'] = 0;
        }

        if(isset($nilai->ulangan_semester['semester_' . $semester])){
            $count['us'] = 1;
        } else {
            $count['us'] = 0;
        }

        return $count;
    }
}
