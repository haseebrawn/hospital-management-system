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
            'department' => $this->whenLoaded('department', function () {
                return ['id' => $this->department?->id, 'name' => $this->department?->name];
            }),
            'roles' => $this->getRoleNames(),
            'created_at' => $this->created_at,
        ];
    }
}
