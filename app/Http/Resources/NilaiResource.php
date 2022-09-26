<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NilaiResource extends JsonResource
{
    public function __construct($resource, $message = null)
    {
        parent::__construct($resource);
        $this->message  = $message;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            $this->mergeWhen($request->routeIs('nilai.index') == false, [
                'status' => 'success',
                'message' => $this->message,
            ]),
            'nilai' => [
                '_id' => $this->_id,
                'mapel_id' => $this->mapel_id,
                'nama_mapel' => $this->nama_mapel,
                'siswa_id' => $this->siswa_id,
                'nama_siswa' => $this->nama_siswa,
                'latihan_soal' => $this->when(isset($this->latihan_soal), $this->latihan_soal),
                'ulangan_harian' => $this->when(isset($this->ulangan_harian), $this->ulangan_harian),
                'ulangan_tengah_semester' => $this->when(isset($this->ulangan_tengah_semester), $this->ulangan_tengah_semester),
                'ulangan_semester' => $this->when(isset($this->ulangan_semester), $this->ulangan_semester),
            ]
        ];
    }
}
