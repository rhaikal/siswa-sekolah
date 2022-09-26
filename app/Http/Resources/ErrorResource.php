<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ErrorResource extends JsonResource
{
    /**
     * Customize the outgoing response for the resource.
     *
     * @param  \Illuminate\Http\Request
     * @param  \Illuminate\Http\Response
     * @return void
     */
    public function withResponse($request, $response)
    {
        /**
         * Not all prerequisites were met.
         */
        $response->setStatusCode(428, 'Precondition Required');
    }

    
    public function __construct($resource = null, $message = null)
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
            'status' => 'failed',
            'message' => $this->message,
            'errors' => $this->resource
        ];
    }
}
