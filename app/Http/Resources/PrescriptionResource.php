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
            'doctor' => $this->doctor?->name,
            'patient' => $this->patient ? trim(($this->patient->first_name ?? '') . ' ' . ($this->patient->last_name ?? '')) : null,
            'description' => $this->description,
            'medicines' => $this->medicines,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'medicine_id' => $item->medicine_id,
                'medicine_name' => $item->medicine_name,
                'dosage' => $item->dosage,
                'frequency' => $item->frequency,
                'duration' => $item->duration,
                'quantity' => $item->quantity,
                'instructions' => $item->instructions,
            ])),
            'status' => $this->status,
            'created_at' => $this->created_at->format('d-m-Y H:i'),
        ];
    }
}
