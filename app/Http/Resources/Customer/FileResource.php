<?php

namespace App\Http\Resources\Customer;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
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
            'name' => $this->name,
            'size' => $this->size,
            'folder_id' => $this->folder_id,
            'created_at' => Carbon::make($this->created_at)->format('d.m.Y H:i'),
            'updated_at' => Carbon::make($this->updated_at)->format('d.m.Y H:i'),
        ];
    }
}
