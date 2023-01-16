<?php

namespace App\Http\Resources;

use App\Http\Resources\Customer\FileResource;
use App\Http\Resources\Customer\FolderResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ExplorerResource extends JsonResource
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
            'select_folder' => (new FolderResource($this->resource))->toArray($request),
            'folders' => FolderResource::collection($this->childrenFolders)->toArray($request),
            'files' => FileResource::collection($this->files)->toArray($request),
        ];
    }
}
