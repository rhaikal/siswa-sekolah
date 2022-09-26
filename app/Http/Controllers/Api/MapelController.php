<?php

namespace App\Http\Controllers\Api;

use App\Models\Mapel;
use App\Models\Siswa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\MapelResource;
use Illuminate\Support\Facades\Validator;

class MapelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return MapelResource::collection(Mapel::paginate(10));
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
            'nama' => 'required|string',
            'slug' => 'string|unique:mapel,slug',
            'guru' => 'string|regex:/\w$/'
        ]);

        if($validator->fails()){
            return new ErrorResource($validator->errors(), 'gagal menambahkan collection mapel');
        }

        $validatedData = $validator->validated();

        $data = $this->olahMapel($validatedData);

        $mapel = Mapel::create($data);
        
        return new MapelResource($mapel, 'berhasil menambahkan collection mapel baru');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mapel  $mapel
     * @return \Illuminate\Http\Response
     */
    public function show(Mapel $mapel)
    {
        return new MapelResource($mapel, 'berhasil mengambil collection mapel');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mapel  $mapel
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Mapel $mapel)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'string',
            'slug' => 'string|unique:mapel,slug',
            'guru' => 'string|regex:/\w$/'
        ]);

        if($validator->fails()){
            return new ErrorResource($validator->errors(), 'gagal mengubah collection mapel');
        }

        $validatedData = $validator->validated();

        $data = $this->olahMapel($validatedData);

        $mapel->update($data);
        
        return new MapelResource($mapel, 'berhasil mengubah collection mapel');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mapel  $mapel
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mapel $mapel)
    {   
        $slug = strtolower($mapel->slug);
        $nilai = $mapel->nilai;
        foreach($nilai as $nli){
            $siswa = $nli->siswa;
            for ($i=0; $i < $siswa->kelas['semester']; $i++) { 
                $siswa->unset('penilaian.semester_'. ($i + 1) . '.'. $slug);
            }
        }

        $mapel->nilai()->whereIn('mapel_id', [$mapel->_id])->delete();

        $mapel->delete();

        return response()->json([
            'status' => true,
            'message' => 'berhasil menghapus collection mapel',
        ]);
    }

    public function olahMapel($processedData)
    {
        if(isset($processedData['nama'])){
            $data['nama'] = $processedData['nama'];
        }
        
        if(isset($processedData['slug'])){
            $data['slug'] = $processedData['slug'];
        } else {
            $data['slug'] = str($processedData['nama'])->slug();
        }
        
        if(isset($processedData['guru'])){
            if(strpos($processedData['guru'], ',') !== false){
                $processedData['guru'] = preg_replace('/,\s+/', ',',$processedData['guru']);
                $processedData['guru'] = explode(',', $processedData['guru']);
            } else {
                $processedData['guru'] = [$processedData['guru']];
            }
            $data['guru'] = $processedData['guru'];
        }

        return $data;
    }
}
