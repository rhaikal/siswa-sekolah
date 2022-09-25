<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MapelResource extends JsonResource
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
            $this->mergeWhen($request->routeIs('mapel.index') == false, [
                'status' => 'success',
                'message' => $this->message,
            ]),
            'mapel' => [
                '_id' => $this->_id,
                'nama' => $this->nama,
                'slug' => $this->slug,
                'guru' => $this->when(isset($this->guru), $this->guru),
            ]
        ];
    }
}
