<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class KelasResource extends JsonResource
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
            $this->mergeWhen($request->routeIs('kelas.index') == false, [
                'status' => 'success',
                'message' => $this->message,
            ]),
            'kelas' => [
                '_id' => $this->_id,
                'jurusan' => $this->jurusan,
                'tahun_ajar' => $this->tahun_ajar,
                'ruang' => $this->when((isset($this->ruang)), $this->ruang),
                'walikelas' => $this->when((isset($this->walikelas)), $this->walikelas),
                'kapasitas' => $this->kapasitas,
                'siswa' => $this->when((isset($this->siswa) && ($request->routeIs('kelas.index') == false)), $this->siswa),
            ],
        ];
    }
}
