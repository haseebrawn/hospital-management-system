<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient' => $this->patient->name,
            'doctor' => $this->doctor->name,
            'scheduled_at' => $this->scheduled_at,
            'status' => $this->status,
            'created_by' => $this->createdBy?->name,
        ];
    }
}
