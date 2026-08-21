<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'status' => [
                'value' => $this->status,
                'label' => $this->status->label(),
            ]
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}