<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrescriptionResource extends JsonResource
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
            'appointment_id' => $this->appointment_id,
            'doctor' => $this->doctor->name,
            'patient' => $this->patient->name,
            'description' => $this->description,
            'medicines' => $this->medicines,
            'created_at' => $this->created_at->format('d-m-Y H:i'),
        ];
    }
}
