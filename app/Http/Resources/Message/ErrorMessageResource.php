<?php

namespace App\Http\Resources\Message;

use Illuminate\Http\Resources\Json\JsonResource;

class ErrorMessageResource extends JsonResource
{
    public function __construct(protected string $message)
    {
        parent::__construct([$message]);
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'status' => 'fail',
            'message' => $this->message,
        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function toResponse($request)
    {
        return parent::toResponse($request)->setStatusCode(460  );
    }
}
