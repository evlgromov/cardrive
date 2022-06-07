<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CarRentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user' => $this->user->only('id', 'name'),
            'car' => $this->car->makeHidden('created_at', 'updated_at'),
            'rented_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
