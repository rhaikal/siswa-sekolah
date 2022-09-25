<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SiswaResource extends JsonResource
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
            $this->mergeWhen($request->routeIs('siswa.index') == false, [
                'status' => 'success',
                'message' => $this->message,
            ]),
            'siswa' => [
                '_id' => $this->_id,
                'nis' => $this->nis,
                'nama' => $this->nama,
                'kelas' => [
                    '_id' => $this->kelas_id,
                    'semester' => $this->kelas['semester'],
                    'jurusan' => $this->kelas['jurusan'],
                ],
                $this->mergeWhen($request->routeIs('siswa.index') == false, [
                    'jenis_kelamin' => $this->jenis_kelamin,
                    'agama' => $this->agama,
                    'alamat' => $this->alamat,
                    'foto' => $this->when(isset($this->foto), $this->foto),
                    'penilaian' => $this->when(isset($this->penilaian), $this->penilaian)
                ])
            ]
        ];
    }
}
